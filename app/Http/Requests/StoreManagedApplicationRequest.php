<?php

namespace App\Http\Requests;

use App\Services\UrlValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'base_url' => [
                'required',
                'string',
                'max:255',
                'url',
                Rule::unique('managed_applications'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $urlService = app(UrlValidationService::class);
                    $allowPrivate = $urlService->shouldAllowPrivateNetworks();
                    
                    $result = $urlService->validate($value, $allowPrivate);
                    
                    if (!$result['valid']) {
                        $fail(implode(' ', $result['errors']));
                    }
                },
            ],
            'environment' => ['required', Rule::in(['local', 'development', 'staging', 'production'])],
            'timezone' => ['required', 'string', 'timezone', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'verify_ssl' => ['boolean'],
            'timeout_seconds' => ['integer', 'min:5', 'max:120'],
            'max_retries' => ['integer', 'min:0', 'max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Application name is required.',
            'name.max' => 'Application name must not exceed 255 characters.',
            'base_url.required' => 'Base URL is required.',
            'base_url.url' => 'Base URL must be a valid URL.',
            'base_url.unique' => 'This URL is already registered.',
            'environment.required' => 'Environment is required.',
            'environment.in' => 'Environment must be one of: local, development, staging, production.',
            'timezone.required' => 'Timezone is required.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'timeout_seconds.min' => 'Timeout must be at least 5 seconds.',
            'timeout_seconds.max' => 'Timeout must not exceed 120 seconds.',
            'max_retries.max' => 'Max retries must not exceed 10.',
        ];
    }

    /**
     * Get validated data with additional processing.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        // Normalize base URL (remove trailing slash)
        if (isset($data['base_url'])) {
            $data['base_url'] = rtrim($data['base_url'], '/');
        }
        
        // Set defaults
        $data['verify_ssl'] = $data['verify_ssl'] ?? true;
        $data['timeout_seconds'] = $data['timeout_seconds'] ?? 30;
        $data['max_retries'] = $data['max_retries'] ?? 3;
        
        return $data;
    }
}
