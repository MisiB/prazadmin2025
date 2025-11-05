# Daily Task Reminder System

## Overview

The Daily Task Reminder system automatically sends email notifications to users about their outstanding tasks every day at 8:00 AM.

---

## Features

✅ **Automated Daily Emails** - Sent every morning at 8:00 AM  
✅ **Task Breakdown** - Separates pending and ongoing tasks  
✅ **Task Summary** - Shows total tasks and hours  
✅ **Beautiful Email Template** - Professional markdown-based design  
✅ **Manual Trigger** - Can be run manually for testing  
✅ **User-Specific** - Can send to specific user for testing  

---

## What Gets Sent

Each email includes:

1. **Summary**
   - Total outstanding tasks count
   - Pending tasks count
   - Ongoing tasks count
   - Total hours for all tasks

2. **Pending Tasks List**
   - Task name
   - Hours allocated
   - Day of week
   - Comments (if any)

3. **Ongoing Tasks List**
   - Task name
   - Hours allocated
   - Day of week
   - Comments (if any)

4. **Action Button**
   - Link to user's calendar

5. **Productivity Tips**
   - Helpful reminders

---

## Email Criteria

**Users receive emails if they have:**
- Tasks with status: `pending` OR `ongoing`
- Tasks in the current week's calendar

**Users DON'T receive emails if:**
- No outstanding tasks
- All tasks are `completed` or `cancelled`
- No email address in profile

---

## Manual Usage

### Send to All Users
```bash
php artisan tasks:send-daily-reminders
```

### Send to Specific User (for testing)
```bash
php artisan tasks:send-daily-reminders --user-id=9c8ce0e6-c173-4a9a-8483-0f9ad09d33b0
```

---

## Scheduling

The command is automatically scheduled to run daily at **8:00 AM**.

**Configured in:** `routes/console.php`

```php
Schedule::command('tasks:send-daily-reminders')->dailyAt('08:00');
```

### Change Schedule Time

Edit `routes/console.php`:

```php
// Run at 7:00 AM
Schedule::command('tasks:send-daily-reminders')->dailyAt('07:00');

// Run at 6:00 PM
Schedule::command('tasks:send-daily-reminders')->dailyAt('18:00');

// Run twice daily (8 AM and 5 PM)
Schedule::command('tasks:send-daily-reminders')->twiceDaily(8, 17);

// Run every Monday at 9 AM
Schedule::command('tasks:send-daily-reminders')->weeklyOn(1, '9:00');
```

---

## Running the Scheduler

### Development (Local)
```bash
php artisan schedule:work
```

### Production (Server)

Add to your crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

For Windows Task Scheduler, create a task that runs:
```bash
php artisan schedule:run
```
Every minute.

---

## Testing

### 1. Test Email Template
```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$pendingTasks = collect([
    (object)['name' => 'Test Task 1', 'hours' => 4, 'day' => 'Monday', 'status' => 'pending', 'comment' => 'Test comment'],
    (object)['name' => 'Test Task 2', 'hours' => 3, 'day' => 'Tuesday', 'status' => 'pending', 'comment' => null],
]);
$ongoingTasks = collect([
    (object)['name' => 'Test Task 3', 'hours' => 2, 'day' => 'Wednesday', 'status' => 'ongoing', 'comment' => null],
]);

Mail::to($user->email)->send(new App\Mail\DailyTaskReminderMail($user, $pendingTasks, $ongoingTasks, 9));
```

### 2. Test Command with Specific User
```bash
php artisan tasks:send-daily-reminders --user-id=YOUR_USER_ID
```

### 3. Check Command Output
```bash
php artisan tasks:send-daily-reminders
```

Expected output:
```
Starting daily task reminder process...
✓ Sent reminder to John Doe (john@example.com) - 5 task(s)
✓ Sent reminder to Jane Smith (jane@example.com) - 3 task(s)

=== Summary ===
Total users processed: 50
Emails sent: 35
Users with no outstanding tasks: 15
```

---

## Email Configuration

Ensure your `.env` file has proper mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Customization

### Email Subject
Edit `app/Mail/DailyTaskReminderMail.php`:

```php
return new Envelope(
    subject: 'Your Daily Task Summary',
);
```

### Email Template
Edit `resources/views/emails/tasks/daily-reminder.blade.php`

### Task Filtering
Edit `app/Console/Commands/SendDailyTaskReminders.php`:

```php
// Change which statuses trigger emails
if (in_array($task->status, ['pending', 'ongoing'])) {
    $tasks->push($task);
}
```

### Schedule Time
Edit `routes/console.php`:

```php
Schedule::command('tasks:send-daily-reminders')->dailyAt('08:00');
```

---

## Troubleshooting

### Emails Not Sending

**Check mail configuration:**
```bash
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

**Check scheduler is running:**
```bash
php artisan schedule:list
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

### Users Not Receiving Emails

1. **Verify user has email address**
```bash
php artisan tinker
User::whereNull('email')->count();
```

2. **Verify user has outstanding tasks**
```bash
php artisan tasks:send-daily-reminders --user-id=USER_ID
```

3. **Check email in spam folder**

### Schedule Not Running

**Development:**
```bash
# Keep this running
php artisan schedule:work
```

**Production:**
```bash
# Verify cron is set up
crontab -l

# Test schedule manually
php artisan schedule:run
```

---

## Files Created

```
app/
├── Console/Commands/
│   └── SendDailyTaskReminders.php    # Command logic
├── Mail/
│   └── DailyTaskReminderMail.php     # Mailable class
resources/
└── views/emails/tasks/
    └── daily-reminder.blade.php       # Email template
routes/
└── console.php                        # Scheduler configuration
```

---

## Performance Considerations

- **Queue Emails**: For large user bases, consider queueing emails:
  ```php
  Mail::to($user->email)->queue(new DailyTaskReminderMail(...));
  ```

- **Batch Processing**: Process users in batches:
  ```php
  $users->chunk(100, function($chunk) {
      // Process chunk
  });
  ```

- **Rate Limiting**: Add delays between emails:
  ```php
  sleep(1); // Wait 1 second between emails
  ```

---

## Future Enhancements

- [ ] Allow users to opt-out of reminders
- [ ] Add weekly summary option
- [ ] Include overdue tasks in separate section
- [ ] Add task priority indicators
- [ ] Include task completion statistics
- [ ] Allow users to customize reminder time
- [ ] Add SMS notifications option
- [ ] Include calendar attachments

---

## Support

For issues or questions about the daily task reminder system, contact your system administrator.



















