<x-mail::message>
# Good day, {{ $supervisor->name }}!

You have **{{ $completedTasks->count() + $submittedWeeks->count() }}** item(s) awaiting your review and approval.

---

## 📊 Summary

- **Completed Tasks Awaiting Approval:** {{ $completedTasks->count() }}
- **Calendar Weeks Submitted for Review:** {{ $submittedWeeks->count() }}

---

@if($completedTasks->count() > 0)
## ✅ Completed Tasks Awaiting Approval ({{ $completedTasks->count() }})

@foreach($completedTasks as $task)
<x-mail::panel>
**{{ $task->title }}**

- **Assigned to:** {{ $task->user->name }} {{ $task->user->surname }}
- **Date:** {{ $task->calendarday ? \Carbon\Carbon::parse($task->calendarday->maindate)->format('M d, Y') : 'N/A' }}
- **Status:** Completed (Pending Approval)
@if($task->description)
- **Description:** {{ \Illuminate\Support\Str::limit($task->description, 100) }}
@endif
</x-mail::panel>
@endforeach
@endif

@if($submittedWeeks->count() > 0)
## 📅 Calendar Weeks Submitted for Review ({{ $submittedWeeks->count() }})

@foreach($submittedWeeks as $submittedWeek)
@php
    $week = $submittedWeek->calendarweek;
    $user = $submittedWeek->user;
    $weekStart = $week->calendardays->min('maindate');
    $weekEnd = $week->calendardays->max('maindate');
    $tasksCount = $week->calendardays->sum(function($day) {
        return $day->tasks->where('user_id', $submittedWeek->user_id)->count();
    });
@endphp
<x-mail::panel>
**Week of {{ \Carbon\Carbon::parse($weekStart)->format('M d') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }}**

- **Submitted by:** {{ $user->name }} {{ $user->surname }}
- **Total Tasks:** {{ $tasksCount }}
- **Status:** Pending Review
- **Submitted on:** {{ $submittedWeek->created_at->format('M d, Y g:i A') }}
</x-mail::panel>
@endforeach
@endif

---

<x-mail::button :url="route('admin.workflows.approvals.weekytasks')">
Review Tasks
</x-mail::button>

**Please review and approve the items listed above at your earliest convenience.**

Thank you for using our application, we are here to serve!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

