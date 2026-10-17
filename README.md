# Laravel Ops

**Centralized Multi-Application Management Platform for Laravel**

Laravel Ops is a standalone Laravel 11 application that provides centralized monitoring, diagnostics, and operation execution for multiple remote Laravel applications. It eliminates the need to SSH into individual servers or manually run Artisan commands across distributed systems.

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Connector Deployment](#connector-deployment)
- [Security](#security)
- [Development](#development)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Support](#support)

---

## Overview

Managing multiple Laravel applications across different servers can be challenging. Laravel Ops solves this by providing:

- **Centralized Dashboard**: Monitor all your Laravel applications from a single pane of glass
- **Remote Operation Execution**: Run approved Artisan commands on any managed application without SSH access
- **Health Monitoring**: Track application health, uptime, and performance metrics
- **Queue & Scheduler Management**: Monitor queues, retry failed jobs, and manage scheduled tasks
- **Log Viewing**: Access application logs remotely
- **Comprehensive Audit Trail**: Track every operation executed with full logging

### Current Status

**Version:** Stage 9 (In Progress)  
**Stable Features:**
- ✅ Application registry and management
- ✅ Remote connector with HMAC-SHA256 authentication
- ✅ 49 operations across 12 categories
- ✅ Health monitoring and diagnostics
- ✅ Queue and scheduler monitoring
- ✅ Log viewer
- ✅ Two-factor authentication (2FA)
- ✅ Account lockout protection
- ✅ Security audit logging

---

## Architecture

Laravel Ops uses a **two-component architecture**:

### 1. Central Application (This Project)

The central Laravel Ops application:
- Manages registered applications
- Sends signed HTTPS requests to connectors
- Displays dashboards, operation history, and health metrics
- Provides user authentication and authorization
- Maintains audit logs of all operations

### 2. Connector (Deployed on Each Managed App)

A lightweight connector package deployed on each target Laravel application:
- Exposes a versioned API endpoint
- Validates HMAC-signed requests from the central app
- Executes only allowlisted operations within its own Laravel context
- Returns structured responses with exit codes

**Key Principle:** The connector runs within the target application's Laravel context, ensuring operations execute with the correct environment, database, and configuration.

---

## Features

### Application Management
- Register and manage multiple Laravel applications
- Automatic enrollment token system for secure connector setup
- Connection testing and health validation
- Credential rotation without downtime

### Remote Operations (49 Operations Across 12 Categories)

**Cache Operations (10)**
- `cache:clear`, `cache:forget`
- `config:clear`, `route:clear`, `view:clear`, `event:clear`
- `optimize:clear`, `optimize`
- `permission:cache-reset`, `log:clear`

**Database Operations (7)**
- `migrate:status`, `migrate`, `migrate:rollback`
- `migrate:refresh`, `migrate:reset`
- `db:show`, `db:table`, `db:wipe`

**Queue Operations (9)**
- `queue:clear`, `queue:failed`, `queue:flush`
- `queue:forget`, `queue:monitor`, `queue:restart`
- `queue:retry`, `queue:retry-batch`, `queue:work`

**Scheduler Operations (4)**
- `schedule:list`, `schedule:run`, `schedule:test`, `schedule:work`

**Storage Operations (1)**
- `storage:link`

**Application Operations (5)**
- `down`, `up`, `about`, `env`, `key:generate`

**Package Operations (1)**
- `package:discover`

**Authentication Operations (1)**
- `auth:clear-resets`

**Permissions Operations (1)**
- `permission:cache-reset` (Spatie Laravel Permission)

**Route Operations (2)**
- `route:list`, `route:cache`

**Config Operations (2)**
- `config:cache`, `config:show`

**View Operations (1)**
- `view:cache`

### Monitoring & Diagnostics
- **Health Checks**: Automated health monitoring with historical tracking
- **Queue Monitoring**: Real-time queue status, failed job tracking, retry/purge actions
- **Scheduler Monitoring**: View scheduled tasks, run tasks on-demand
- **Log Viewer**: Remote log access with clear functionality

### Security Features
- **Two-Factor Authentication (2FA)**: Email-based 2FA for all user accounts
- **Account Lockout**: Automatic lockout after 5 failed login attempts (15-minute lockout period)
- **Rate Limiting**: Prevents brute-force attacks (5 attempts per minute)
- **Session Security**: Automatic session regeneration for sensitive actions
- **Security Audit Log**: Complete audit trail of all security events
- **Password Confirmation**: Required for sensitive operations (e.g., disabling 2FA)
- **HMAC-SHA256 Signing**: All connector requests are cryptographically signed
- **Replay Protection**: Timestamp-based request validation
- **SSRF Protection**: URL validation service prevents internal network access

---

## Requirements

### Central Application
- **PHP**: ^8.2 (PHP 8.3.x recommended)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache / Nginx
- **Composer**: 2.x
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Connector (Target Applications)
- **Laravel**: 8.x, 9.x, 10.x, or 11.x
- **PHP**: ^8.0
- **Network**: Must be able to make HTTPS requests to the central app

---

## Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-org/laravel-ops.git
cd laravel-ops
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Configure Database

Edit your `.env` file with database credentials:

```env
APP_NAME="Laravel Ops"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost/laravel-ops

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ops
DB_USERNAME=root
DB_PASSWORD=

# Email configuration (required for 2FA)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@laravelops.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Set Permissions (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Windows:** No permission changes needed.

### Step 7: Create First User

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('your-password'),
    'email_verified_at' => now(),
]);
```

Or register via the web interface at `http://localhost/laravel-ops/register`.

---

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | `Laravel Ops` |
| `APP_URL` | Base URL | `http://localhost/laravel-ops` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `laravel_ops` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | (empty) |
| `MAIL_*` | Email configuration | (required for 2FA) |

### Security Configuration

**Account Lockout:**
- Max attempts: 5
- Lockout duration: 15 minutes
- Configurable in `app/Services/AccountLockoutService.php`

**Rate Limiting:**
- 5 login attempts per minute per IP
- Configurable in `app/Providers/RouteServiceProvider.php`

**Session Security:**
- Automatic session regeneration on sensitive actions
- Session lifetime: 120 minutes
- Encrypted sessions: disabled (enable in production)

---

## Usage

### 1. Access the Dashboard

Navigate to `http://localhost/laravel-ops` and log in.

### 2. Register an Application

1. Click **"Add Application"**
2. Fill in application details:
   - **Name**: Friendly name (e.g., "Production API")
   - **Base URL**: Application URL (e.g., `https://api.example.com`)
   - **Environment**: `production`, `staging`, `development`
3. Click **"Register Application"**
4. Note the **API Key** and **API Secret** (shown once)

### 3. Deploy the Connector

See [Connector Deployment](#connector-deployment) section below.

### 4. Test Connection

1. Go to your application's detail page
2. Click **"Test Connection"**
3. Verify the connector responds successfully

### 5. Execute Operations

1. Navigate to an application
2. Click **"Operations"** → **"Execute Operation"**
3. Select an operation from the dropdown
4. Fill in required parameters (if any)
5. Review risk warnings
6. Confirm and execute

### 6. Monitor Health

1. Navigate to an application
2. Click **"Health Checks"**
3. Click **"Run Health Check"** to test current status
4. View historical health data

### 7. Monitor Queues

1. Navigate to an application
2. Click **"Queue Monitor"**
3. View queue status, failed jobs, and pending jobs
4. Retry failed jobs or purge the queue

### 8. View Logs

1. Navigate to an application
2. Click **"Logs"**
3. View recent log entries
4. Clear logs if needed

---

## Connector Deployment

The connector package can be deployed to each target Laravel application via Composer or manual copy.

### Method 1: Composer Installation (Recommended)

**Step 1: Install via Composer**

```bash
cd /path/to/target-app
composer require laravel-ops/connector
```

**Step 2: Publish Configuration (Optional)**

```bash
php artisan vendor:publish --tag=connector-config
```

**Step 3: Configure Environment Variables**

Add to the target app's `.env`:

```env
CONNECTOR_API_KEY={api_key_from_laravel_ops}
CONNECTOR_API_SECRET={api_secret_from_laravel_ops}
```

### Method 2: Manual Installation

**Step 1: Copy Connector to Target App**

```bash
# From Laravel Ops root directory
cp -r connector /path/to/target-app/laravel-ops-connector
```

**Step 2: Configure Target App's composer.json**

Add to the target app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./laravel-ops-connector",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "laravel-ops/connector": "*"
    }
}
```

**Step 3: Install the Package**

```bash
cd /path/to/target-app
composer update laravel-ops/connector
```

**Step 4: Configure Environment Variables**

Add to the target app's `.env`:

```env
CONNECTOR_API_KEY={api_key_from_laravel_ops}
CONNECTOR_API_SECRET={api_secret_from_laravel_ops}
```

### Final Steps (Both Methods)

**Step 1: Clear Caches**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

**Step 2: Verify Installation**

Test the connector endpoint:

```bash
curl https://your-app.com/laravel-ops-connector/health
```

Expected response:

```json
{
    "status": "ok",
    "timestamp": "2026-10-17T12:00:00Z"
}
```

---

## Security

### Authentication

- **Email-based 2FA**: All users can enable two-factor authentication
- **Account Lockout**: Automatic lockout after 5 failed attempts
- **Rate Limiting**: Prevents brute-force attacks
- **Session Management**: Automatic session regeneration

### API Security

- **HMAC-SHA256 Signing**: All requests are cryptographically signed
- **Timestamp Validation**: Requests expire after 5 minutes
- **Replay Protection**: Nonce-based request deduplication
- **SSRF Protection**: URL validation prevents internal network access

### Audit Logging

All security events are logged:
- Login success/failure
- 2FA enabled/disabled/verified
- Account lockout
- Password changes
- Sensitive operations

View audit logs: **User Menu** → **Security Audit Log**

### Best Practices

1. **Enable 2FA**: Require all users to enable two-factor authentication
2. **Rotate Credentials**: Regularly rotate API keys and secrets
3. **Monitor Audit Logs**: Review audit logs for suspicious activity
4. **Use HTTPS**: Always use HTTPS in production
5. **Restrict Access**: Use IP whitelisting for the central app
6. **Strong Passwords**: Enforce strong password policies
7. **Regular Updates**: Keep Laravel and dependencies updated

---

## Development

### Local Development Setup

1. **Start Your Stack**: Launch your preferred local development environment (Apache/Nginx + MySQL)
2. **Create Database**: Create the `laravel_ops` database in your MySQL client
3. **Run Migrations**: `php artisan migrate`
4. **Start Development**: Access via your configured web server URL

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### Code Style

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

### Database Schema

**Key Tables:**
- `users`: User accounts with 2FA support
- `managed_applications`: Registered Laravel applications
- `enrollment_tokens`: One-time tokens for connector enrollment
- `health_checks`: Application health history
- `operation_results`: Executed operations and their results
- `operation_logs`: Detailed logs for each operation
- `audit_logs`: Security audit trail

---

## Troubleshooting

### Connection Issues

**Problem:** "Connection refused" when testing connector

**Solutions:**
1. Verify the connector is deployed correctly
2. Check API credentials match between central app and connector
3. Ensure the target app's URL is accessible from the central app
4. Check firewall rules allow HTTPS traffic

**Problem:** "Invalid signature" errors

**Solutions:**
1. Verify API key and secret match exactly
2. Check server clocks are synchronized (NTP)
3. Regenerate credentials if needed

### 2FA Issues

**Problem:** Not receiving 2FA codes

**Solutions:**
1. Verify email configuration in `.env`
2. Check spam folder
3. Review Laravel logs: `storage/logs/laravel.log`
4. Test email configuration with Mailtrap

**Problem:** Account locked out

**Solutions:**
1. Wait 15 minutes for automatic unlock
2. Or run SQL: `UPDATE users SET locked_until = NULL, login_attempts = 0 WHERE email = 'your@email.com';`

### Operation Issues

**Problem:** Operation fails with "Command not found"

**Solutions:**
1. Verify the Artisan command exists on the target app
2. Check the connector's allowlist configuration
3. Refresh operations list: `php artisan ops:refresh-operations`

**Problem:** Operation times out

**Solutions:**
1. Increase PHP timeout in `php.ini`
2. Check target app's queue worker status
3. Review operation logs for details

### Database Issues

**Problem:** Migration fails

**Solutions:**
1. Ensure database exists: `CREATE DATABASE laravel_ops;`
2. Check database credentials in `.env`
3. Run `php artisan migrate:fresh` (WARNING: deletes all data)

---

## License

This project is proprietary software. All rights reserved.

**Copyright © 2026 OP Virtual Technology Systems Ltd. All rights reserved.**

Licensed to Olusegun (paultnd@gmail.com)

---

## Support

For issues, questions, or contributions:

- **Developer**: Olusegun
- **Company**: OP Virtual Technology Systems Ltd
- **Email**: paultnd@gmail.com
- **Issue Tracker**: [GitHub Issues](https://github.com/your-org/laravel-ops/issues)

---

## Credits

**Built With:**
- [Laravel 11](https://laravel.com)
- [Bootstrap 5](https://getbootstrap.com)
- [PHP 8.3](https://www.php.net)

**Architecture:**
- Two-component design (Central App + Connector)
- HMAC-SHA256 request signing
- Email-based 2FA with audit logging

**Author:**
- Olusegun
- OP Virtual Technology Systems Ltd
- paultnd@gmail.com

---

## Changelog

### Version 1.0 (Stage 9) - 2026-10-17
- ✅ Two-factor authentication (2FA)
- ✅ Account lockout protection
- ✅ Security audit logging
- ✅ Rate limiting
- ✅ Session security enhancements

### Version 0.9 (Stage 8) - 2026-10-17
- ✅ Queue monitoring and management
- ✅ Scheduler monitoring
- ✅ Log viewer
- ✅ 49 operations across 12 categories

### Version 0.8 (Stage 7) - 2026-10-16
- ✅ Log viewer with clear functionality
- ✅ Additional cache operations

### Version 0.7 (Stage 6) - 2026-10-16
- ✅ Cache and database operations
- ✅ Parameter input system for operations
- ✅ 40 operations working

### Version 0.6 (Stage 5) - 2026-10-16
- ✅ Remote operation execution
- ✅ Dynamic operation discovery
- ✅ Operation logging and history

### Version 0.5 (Stage 4) - 2026-10-16
- ✅ Health monitoring system
- ✅ Connection testing

### Version 0.4 (Stage 3) - 2026-10-16
- ✅ Remote connector package
- ✅ HMAC-SHA256 authentication
- ✅ Enrollment token system

### Version 0.3 (Stage 2) - 2026-10-16
- ✅ User authentication (login/register)
- ✅ Application registry
- ✅ SSRF protection
- ✅ Encrypted credentials

### Version 0.2 (Stage 1) - 2026-10-16
- ✅ Laravel bootstrap
- ✅ MySQL database setup
- ✅ Dashboard
- ✅ Basic health check

---

**Last Updated:** October 17, 2026
