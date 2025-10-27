# Microsoft Graph Setup Guide

## Quick Setup Steps

### 1. **Authenticate Microsoft Graph**
Visit the authentication URL in your browser:
```
https://prazcrmadmin.test/connect
```

This will redirect you to Microsoft's OAuth page where you need to:
- Sign in with your Office 365 account
- Grant permissions to the application
- Complete the OAuth flow

### 2. **Keep Token Alive**
After authentication, run this command to refresh the token:
```bash
php artisan msgraph:keep-alive
```

### 3. **Test the Command**
Once authenticated, test the email processing:
```bash
php artisan app:process-support-emails --dry-run --limit=1
```

## Environment Configuration

Make sure your `.env` file has these Microsoft Graph settings:

```env
MSGRAPH_CLIENT_ID=your-azure-app-client-id
MSGRAPH_SECRET_ID=your-azure-app-secret
MSGRAPH_TENANT_ID=your-azure-tenant-id
MSGRAPH_OAUTH_URL=https://prazcrmadmin.test/msgraph/oauth
MSGRAPH_LANDING_URL=https://prazcrmadmin.test/msgraph
```

## Azure App Registration Requirements

Your Azure app registration needs these permissions:
- `Mail.Read` - Read user mail
- `Mail.ReadWrite` - Read and write user mail
- `User.Read` - Read user profile

## Troubleshooting

### Error: "Microsoft Graph authentication required"
**Solution**: Visit `https://prazcrmadmin.test/connect` and complete the OAuth flow.

### Error: "Authentication expired or invalid"
**Solution**: Run `php artisan msgraph:keep-alive` to refresh the token.

### Error: "Bearer HTTP/1.0 302 Found"
**Solution**: This indicates the token is expired. Re-authenticate at `/connect`.

## Scheduling the Command

Add to your Laravel scheduler (`app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule)
{
    // Process support emails every 5 minutes
    $schedule->command('app:process-support-emails')
             ->everyFiveMinutes()
             ->withoutOverlapping();
             
    // Keep Microsoft Graph token alive daily
    $schedule->command('msgraph:keep-alive')
             ->daily();
}
```

## Testing Without Authentication

If you want to test the PrismPHP agent without Microsoft Graph, you can create a test command that simulates email data:

```bash
php artisan make:command TestEmailAnalysis
```

Then use the agent directly with sample email data to verify the AI analysis works correctly.

