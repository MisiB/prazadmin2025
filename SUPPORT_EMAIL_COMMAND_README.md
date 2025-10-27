# Support Email Processing Command

This Laravel command processes emails from Office 365 Outlook support inbox and creates tickets using PrismPHP AI agent for intelligent analysis.

## Features

- **Office 365 Integration**: Reads emails from Microsoft Graph API
- **AI-Powered Analysis**: Uses PrismPHP agent to analyze email content
- **Automatic Ticket Creation**: Creates tickets based on email analysis
- **Smart Filtering**: Only creates tickets for actual support requests
- **Priority Detection**: Automatically determines ticket priority
- **Issue Classification**: Categorizes issues by type (Authentication, Email, System, etc.)

## Usage

### Basic Usage
```bash
php artisan app:process-support-emails
```

### With Options
```bash
# Specify support email address
php artisan app:process-support-emails --support-email=support@yourcompany.com

# Limit number of emails to process
php artisan app:process-support-emails --limit=5

# Dry run mode (no tickets created)
php artisan app:process-support-emails --dry-run
```

### Command Options

- `--support-email`: The support email address to monitor (default: support@company.com)
- `--limit`: Maximum number of emails to process (default: 10)
- `--dry-run`: Run without creating tickets (useful for testing)

## Prerequisites

1. **Microsoft Graph Configuration**: Ensure your `.env` file has the required MS Graph settings:
   ```
   MSGRAPH_CLIENT_ID=your-client-id
   MSGRAPH_SECRET_ID=your-client-secret
   MSGRAPH_TENANT_ID=your-tenant-id
   MSGRAPH_OAUTH_URL=https://yourdomain.com/msgraph/oauth
   MSGRAPH_LANDING_URL=https://yourdomain.com/msgraph
   ```

2. **Database Setup**: Ensure you have:
   - Issue groups created
   - Issue types created
   - At least one department

3. **Authentication**: Complete the Microsoft Graph OAuth flow by visiting `/connect` in your application

## How It Works

1. **Email Retrieval**: Fetches unread emails from the specified support inbox
2. **AI Analysis**: Uses PrismPHP agent to analyze email content for:
   - Support request detection
   - Priority level determination
   - Issue type classification
   - Title and description extraction
3. **Ticket Creation**: Creates tickets in the system for emails identified as support requests
4. **Email Management**: Marks processed emails as read

## AI Analysis Features

The PrismPHP agent analyzes emails for:

### Support Request Detection
Keywords: help, support, issue, problem, error, bug, broken, not working, unable to, cannot, failed, trouble, assistance

### Priority Detection
- **High**: urgent, critical, emergency
- **Medium**: default priority
- **Low**: low, minor

### Issue Type Classification
- **Authentication**: login, password issues
- **Email**: email, mail problems
- **System**: system, server issues
- **Database**: database, data problems
- **Network**: network, connection issues
- **General**: default category

## Scheduling

To run this command automatically, add it to your Laravel scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:process-support-emails')
             ->everyFiveMinutes()
             ->withoutOverlapping();
}
```

## Error Handling

The command includes comprehensive error handling:
- Logs all errors to Laravel's log system
- Continues processing other emails if one fails
- Provides detailed console output for monitoring

## Testing

### Test PrismPHP Agent (No Authentication Required)
Test the AI analysis functionality without Microsoft Graph authentication:
```bash
php artisan app:test-email-analysis
```

This command tests the PrismPHP agent with sample emails to verify:
- Support request detection
- Priority determination
- Issue type classification
- Title and description extraction

### Test Full Command (Requires Authentication)
Test the complete email processing in dry-run mode:
```bash
php artisan app:process-support-emails --dry-run --limit=1
```

This will show you what would happen without actually creating tickets or marking emails as read.

### Authentication Issues
If you see errors like "Bearer HTTP/1.0 302 Found" or "Microsoft Graph authentication required":

1. **Visit the authentication URL**: `https://prazcrmadmin.test/connect`
2. **Complete OAuth flow** with your Office 365 account
3. **Refresh token**: `php artisan msgraph:keep-alive`
4. **Test again**: `php artisan app:process-support-emails --dry-run`
