<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class UrlValidationService
{
    /**
     * Private IP ranges that should be blocked (SSRF protection).
     */
    private const BLOCKED_RANGES = [
        // IPv4
        '127.0.0.0/8'    => 'Loopback',
        '10.0.0.0/8'     => 'Private network',
        '172.16.0.0/12'  => 'Private network',
        '192.168.0.0/16' => 'Private network',
        '169.254.0.0/16' => 'Link-local (includes cloud metadata)',
        '0.0.0.0/8'      => 'Current network',
        
        // IPv6
        '::1/128'        => 'IPv6 loopback',
        'fc00::/7'       => 'IPv6 private',
        'fe80::/10'      => 'IPv6 link-local',
    ];

    /**
     * Cloud metadata endpoints to block.
     */
    private const BLOCKED_HOSTS = [
        '169.254.169.254',  // AWS, GCP, Azure metadata
        'metadata.google.internal',
        'metadata',
        'localhost',
        'localhost.localdomain',
    ];

    /**
     * Allowed schemes.
     */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Validate a URL for safety and correctness.
     *
     * @param string $url The URL to validate
     * @param bool $allowPrivateNetworks Whether to allow private networks (dev only)
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validate(string $url, bool $allowPrivateNetworks = false): array
    {
        $errors = [];

        // Check if URL is valid
        $parsedUrl = parse_url($url);
        
        if ($parsedUrl === false) {
            $errors[] = 'Invalid URL format.';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check scheme
        if (!isset($parsedUrl['scheme']) || !in_array(strtolower($parsedUrl['scheme']), self::ALLOWED_SCHEMES)) {
            $errors[] = 'URL must use HTTP or HTTPS protocol.';
        }

        // Check host
        if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
            $errors[] = 'URL must include a valid hostname.';
            return ['valid' => false, 'errors' => $errors];
        }

        $host = strtolower($parsedUrl['host']);

        // Check against blocked hosts (skip if allowing private networks for development)
        if (!$allowPrivateNetworks && in_array($host, self::BLOCKED_HOSTS)) {
            $errors[] = 'URL points to a blocked host (localhost or metadata endpoint).';
        }

        // Check for IP addresses
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!$allowPrivateNetworks && $this->isPrivateIp($host)) {
                $errors[] = 'URL points to a private or reserved IP address.';
            }
        } else {
            // Resolve hostname to IP and check
            if (!$allowPrivateNetworks) {
                $ip = gethostbyname($host);
                if ($ip !== $host && $this->isPrivateIp($ip)) {
                    $errors[] = 'URL resolves to a private or reserved IP address.';
                }
            }
        }

        // Check for fragments
        if (isset($parsedUrl['fragment'])) {
            $errors[] = 'URL must not contain fragments (#).';
        }

        // Check for user info
        if (isset($parsedUrl['user']) || isset($parsedUrl['pass'])) {
            $errors[] = 'URL must not contain credentials (user:pass@).';
        }

        // Validate as filter
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = 'URL does not pass standard validation.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if an IP address is private or reserved.
     */
    private function isPrivateIp(string $ip): bool
    {
        foreach (self::BLOCKED_RANGES as $range => $description) {
            if ($this->ipInRange($ip, $range)) {
                Log::warning('SSRF attempt blocked', [
                    'ip' => $ip,
                    'range' => $range,
                    'description' => $description,
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is within a CIDR range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        
        // Handle IPv4
        if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;
            return ($ip & $mask) == $subnet;
        }
        
        // Handle IPv6 (simplified check)
        if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // For IPv6, we'll use a simpler approach
            // Convert both to binary and compare
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            
            if ($ipBin === false || $subnetBin === false) {
                return false;
            }
            
            // Compare the first $bits bits
            $bytes = intval($bits / 8);
            for ($i = 0; $i < $bytes; $i++) {
                if ($ipBin[$i] !== $subnetBin[$i]) {
                    return false;
                }
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Validate URL and throw exception if invalid.
     *
     * @throws \InvalidArgumentException
     */
    public function validateOrFail(string $url, bool $allowPrivateNetworks = false): void
    {
        $result = $this->validate($url, $allowPrivateNetworks);
        
        if (!$result['valid']) {
            throw new \InvalidArgumentException(implode(' ', $result['errors']));
        }
    }

    /**
     * Check if private networks are allowed (based on environment).
     */
    public function shouldAllowPrivateNetworks(): bool
    {
        // Check for explicit config value first
        if (config('app.env') === 'local' || config('app.env') === 'development') {
            return env('ALLOW_PRIVATE_NETWORKS', true);
        }
        
        // In production, never allow private networks unless explicitly configured
        return env('ALLOW_PRIVATE_NETWORKS', false);
    }
}
