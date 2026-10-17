# Quick Test Guide for 2FA and Security Features

## Prerequisites
1. Run migrations:
```bash
php artisan migrate
```

2. Configure email in `.env` (use Mailtrap for testing):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@laravelops.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Test 1: Normal Login (No 2FA)
**Goal:** Verify existing login still works

1. Go to http://localhost/laravel-ops/register
2. Register a new account
3. Log in with your credentials
4. ✅ Should log in successfully without 2FA prompt
5. Check audit log: Should show "login.success" event

## Test 2: Enable 2FA
**Goal:** Enable 2FA for your account

1. While logged in, click your name (top-right)
2. Click "Two-Factor Authentication"
3. Click "Enable Two-Factor Authentication"
4. ✅ Should see success message
5. Check your email (Mailtrap inbox)
6. ✅ Should receive email with 6-digit code
7. Check audit log: Should show "2FA Enabled" event

## Test 3: Login with 2FA
**Goal:** Verify 2FA login flow

1. Log out
2. Go to login page
3. Enter your email and password
4. ✅ Should redirect to 2FA challenge page
5. Check email for new 6-digit code
6. Enter the code
7. ✅ Should log in successfully
8. Check audit log: Should show "2FA Verified" event

## Test 4: Invalid 2FA Code
**Goal:** Verify invalid code handling

1. Log out and log in again
2. On 2FA challenge page, enter wrong code (e.g., 000000)
3. ✅ Should show error "The verification code is invalid or has expired"
4. Check audit log: Should show "2FA Failed" event
5. Enter correct code
6. ✅ Should log in successfully

## Test 5: Resend 2FA Code
**Goal:** Verify code resend functionality

1. Log out and log in again
2. On 2FA challenge page, click "Resend Code"
3. ✅ Should see success message
4. Check email
5. ✅ Should receive new code
6. Enter the new code
7. ✅ Should log in successfully
8. Check audit log: Should show "2FA Code Resent" event

## Test 6: Disable 2FA
**Goal:** Disable 2FA for your account

1. While logged in, go to 2FA settings
2. Enter your password
3. Click "Disable Two-Factor Authentication"
4. ✅ Should see success message
5. Log out and log in again
6. ✅ Should log in without 2FA prompt
7. Check audit log: Should show "2FA Disabled" event

## Test 7: Account Lockout
**Goal:** Verify account lockout after failed attempts

1. Go to login page
2. Enter wrong password 5 times
3. ✅ On 5th attempt, should see "Too many failed attempts. Your account is locked for 15 minutes"
4. Try to log in with correct password
5. ✅ Should still see lockout message
6. Check audit log: Should show multiple "login.failed" events and "account.locked" event
7. Wait 15 minutes (or clear in database)
8. ✅ Should be able to log in again

**Quick unlock (for testing only):**
```sql
UPDATE users SET locked_until = NULL, login_attempts = 0 WHERE email = 'your@email.com';
```

## Test 8: Rate Limiting
**Goal:** Verify rate limiting prevents rapid attempts

1. Go to login page
2. Try to log in 6 times within 1 minute (any email/password)
3. ✅ Should see "Too many login attempts. Please try again in X minutes"
4. Wait for the specified time
5. ✅ Should be able to try again

**Clear rate limit (for testing only):**
```bash
php artisan cache:clear
```

## Test 9: Security Audit Log
**Goal:** Verify audit log displays all events

1. Click your name → "Security Audit Log"
2. ✅ Should see list of all security events
3. ✅ Events should have icons, IP addresses, and timestamps
4. ✅ Events should be paginated (20 per page)
5. Check for these events:
   - Login successful
   - Login failed
   - 2FA enabled
   - 2FA disabled
   - 2FA code sent
   - 2FA verified
   - 2FA failed
   - Account locked

## Test 10: Session Security
**Goal:** Verify session regeneration

1. Log in
2. Note your session ID in browser dev tools (Application → Cookies)
3. Perform a sensitive action (e.g., enable 2FA)
4. Check session ID again
5. ✅ Session ID should have changed
6. Log out
7. ✅ Session should be invalidated
8. Try to go back to protected page
9. ✅ Should redirect to login

## Test 11: Password Confirmation
**Goal:** Verify password confirmation for disabling 2FA

1. Enable 2FA if not already enabled
2. Go to 2FA settings
3. Enter wrong password when trying to disable
4. ✅ Should show error "The provided password is incorrect"
5. Enter correct password
6. ✅ Should disable 2FA successfully

## Test 12: Email Verification
**Goal:** Verify email is required for 2FA

1. Register new account
2. Don't verify email
3. Try to enable 2FA
4. ✅ Should work (email verification is separate from 2FA)
5. But verify email for full security

## Expected Results Summary

| Test | Feature | Expected Result |
|------|---------|-----------------|
| 1 | Normal Login | ✅ Works without 2FA |
| 2 | Enable 2FA | ✅ Code sent via email |
| 3 | 2FA Login | ✅ Code required and verified |
| 4 | Invalid Code | ✅ Error shown, logged |
| 5 | Resend Code | ✅ New code sent |
| 6 | Disable 2FA | ✅ Password required |
| 7 | Account Lockout | ✅ Locked after 5 attempts |
| 8 | Rate Limiting | ✅ Throttled after 5/min |
| 9 | Audit Log | ✅ All events recorded |
| 10 | Session Security | ✅ Sessions regenerated |
| 11 | Password Confirm | ✅ Required for disable |
| 12 | Email Verify | ✅ Separate from 2FA |

## Troubleshooting

### Not receiving emails:
- Check Mailtrap configuration
- Verify email credentials in `.env`
- Check spam folder
- Review Laravel logs: `storage/logs/laravel.log`

### Migration fails:
- Check database connection
- Ensure database exists
- Run `php artisan migrate:fresh` (WARNING: deletes all data)

### Rate limit too aggressive:
- Clear cache: `php artisan cache:clear`
- Adjust limits in `app/Providers/RouteServiceProvider.php`

### Account locked:
- Wait 15 minutes
- Or run SQL: `UPDATE users SET locked_until = NULL, login_attempts = 0 WHERE email = 'your@email.com';`

### 2FA not working:
- Check `two_factor_enabled` column in users table
- Verify email is configured
- Check audit log for errors

## Success Criteria

All tests pass if:
- ✅ Can log in without 2FA when disabled
- ✅ Can enable 2FA and receive codes via email
- ✅ Can log in with 2FA code
- ✅ Invalid codes are rejected
- ✅ Can resend codes
- ✅ Can disable 2FA with password
- ✅ Account locks after 5 failed attempts
- ✅ Rate limiting prevents rapid attempts
- ✅ All events appear in audit log
- ✅ Sessions are properly managed
- ✅ Password confirmation required for sensitive actions

## Next Steps

After all tests pass:
1. ✅ 2FA implementation is complete
2. ✅ Security features are working
3. ✅ Audit logging is functional
4. ✅ Ready for production deployment

Remember to:
- Use strong passwords
- Enable 2FA for all users in production
- Monitor audit logs regularly
- Configure proper email service
- Set up log monitoring/alerting
