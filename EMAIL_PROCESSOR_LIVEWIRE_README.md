# Email Processor Livewire Module

A comprehensive Livewire module for processing support emails from Office 365 and creating tickets using PrismPHP AI analysis.

## Features

### 🔐 **Authentication Management**
- Real-time Microsoft Graph authentication status checking
- Clear authentication guidance with direct links
- Token refresh functionality

### 📧 **Email Processing**
- Fetch emails from Office 365 support inbox
- Configurable email limit and support email address
- Real-time email list display with sender information
- Individual email analysis and processing

### 🤖 **AI-Powered Analysis**
- PrismPHP agent integration for intelligent email analysis
- Support request detection
- Priority level determination (high/medium/low)
- Issue type classification (Authentication, Email, System, Database, Network, General)
- Confidence scoring for analysis results

### 🎫 **Ticket Management**
- Automatic ticket creation from analyzed emails
- Real-time ticket tracking and display
- Batch processing capabilities
- Email marking as read after processing

### 🎨 **User Interface**
- Modern, responsive design with dark mode support
- Real-time status updates and error handling
- Interactive analysis modal with detailed results
- Progress indicators and loading states

## Usage

### Access the Module
Visit: `https://prazcrmadmin.test/email-processor`

### Step-by-Step Process

1. **Check Authentication**
   - Click "Check Authentication" to verify Microsoft Graph connection
   - If not authenticated, click the provided link to complete OAuth flow

2. **Configure Settings**
   - Set the support email address to monitor
   - Adjust the email limit (1-50 emails)

3. **Fetch Emails**
   - Click "Fetch Emails" to retrieve emails from Office 365
   - View the list of unread emails with sender details

4. **Analyze Emails**
   - Click "Analyze" on individual emails to see AI analysis results
   - Review the analysis modal for detailed information

5. **Create Tickets**
   - Click "Create Ticket" on emails that require support
   - Or use "Process All" to automatically process all emails

6. **Monitor Results**
   - View created tickets in the "Created Tickets" section
   - Track processing status and ticket numbers

## HTML Content Processing

The module automatically handles HTML content from Microsoft Graph emails:

### HTML Cleaning Features
- **Tag Removal**: Strips all HTML tags using `strip_tags()`
- **Entity Decoding**: Converts HTML entities to readable text using `html_entity_decode()`
- **Content Sanitization**: Removes email headers, signatures, and quoted text
- **Whitespace Normalization**: Cleans up multiple spaces and line breaks
- **Length Limiting**: Truncates long content to prevent database issues

### Example Transformation
**Before (HTML from Microsoft Graph):**
```html
<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body><table><tr><td><div style="font-family:Calibri,Arial,Helvetica,sans-serif;">
Hello,<br><br>I am having trouble logging into the system.<br><br>Please help me.
</div></td></tr></table></body></html>
```

**After (Clean Text):**
```
Hello, I am having trouble logging into the system. Please help me.
```

This ensures that ticket descriptions and titles are clean, readable text without HTML markup.

## Technical Details

### PrismPHP Agent Integration
The module uses PrismPHP Tool for email analysis:

```php
private function initializeAgent(): Tool
{
    return (new Tool)
        ->as('analyze_email_for_ticket')
        ->for('Analyzes email content to determine if it should create a support ticket and extracts relevant information')
        ->withStringParameter('email_content', 'The full content of the email')
        ->withStringParameter('email_subject', 'The subject line of the email')
        ->withStringParameter('sender_email', 'The email address of the sender')
        ->using(function (string $emailContent, string $emailSubject, string $senderEmail): string {
            $analysis = $this->analyzeEmailContent($emailContent, $emailSubject, $senderEmail);
            return json_encode($analysis);
        });
}
```

### Microsoft Graph Integration
Uses the existing `dcblogdev/laravel-microsoft-graph` package for:
- Email retrieval from Office 365
- Authentication management
- Email status updates (marking as read)

### Repository Pattern
Integrates with existing application architecture:
- `iissuelogInterface` for ticket creation
- `iissuetypeInterface` for issue type management
- `iissuegroupInterface` for issue group management

## Error Handling

### Authentication Errors
- Clear error messages for authentication issues
- Direct links to authentication endpoints
- Token refresh functionality

### Processing Errors
- Individual email processing continues if one fails
- Comprehensive error logging
- User-friendly error messages

### Network Errors
- Graceful handling of Microsoft Graph API errors
- Retry mechanisms for transient failures
- Detailed error reporting

## Security Features

- Authentication required for all operations
- Secure token management
- Input validation and sanitization
- CSRF protection through Livewire

## Performance Considerations

- Configurable email limits to prevent memory issues
- Efficient email processing with batch operations
- Real-time updates without page refreshes
- Optimized database queries through repository pattern

## Customization

### Adding New Issue Types
Modify the `determineIssueType()` method to add new classification logic:

```php
private function determineIssueType(string $content): string
{
    if (strpos($content, 'login') !== false || strpos($content, 'password') !== false) {
        return 'Authentication';
    }
    // Add new conditions here
    return 'General';
}
```

### Modifying Analysis Logic
Update the `analyzeEmailContent()` method to change analysis behavior:

```php
private function analyzeEmailContent(string $content, string $subject, string $sender): array
{
    // Modify keyword lists, priority logic, etc.
    $supportKeywords = [
        'help', 'support', 'issue', 'problem', 'error', 'bug', 'broken',
        // Add new keywords here
    ];
    
    // Rest of analysis logic...
}
```

## Troubleshooting

### Common Issues

1. **"Microsoft Graph authentication required"**
   - Visit `/connect` to complete OAuth flow
   - Ensure Azure app has proper permissions

2. **"No issue groups found"**
   - Create issue groups in the configuration section
   - Ensure at least one issue group exists

3. **"Failed to fetch emails"**
   - Check Microsoft Graph authentication
   - Verify support email address exists
   - Check network connectivity

### Debug Mode
Enable detailed logging by checking Laravel logs:
```bash
php artisan pail
```

## Integration with Existing System

The module seamlessly integrates with your existing ticket system:
- Uses existing `Issuelog` model and relationships
- Follows established repository patterns
- Maintains data consistency with current workflows
- Supports existing user permissions and roles
