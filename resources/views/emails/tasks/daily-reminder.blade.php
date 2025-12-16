<x-mail::message>
# Good Morning, {{ $user->name }}!

You have **{{ $pendingTasks->count() + $ongoingTasks->count() }}** outstanding task(s) from previous days that need your attention.

---

## 📋 Task Summary

- **Pending Tasks:** {{ $pendingTasks->count() }}
- **Ongoing Tasks:** {{ $ongoingTasks->count() }}
- **Total Hours:** {{ number_format($totalHours, 1) }} hours

---

@if($pendingTasks->count() > 0)
## ⏳ Pending Tasks ({{ $pendingTasks->count() }})

@foreach($pendingTasks as $task)
<x-mail::panel>
**{{ $task->name }}**

- **Hours:** {{ $task->hours }}
- **Day:** {{ $task->day ?? 'Not specified' }}
- **Status:** Pending
@if($task->comment)
- **Comment:** {{ $task->comment }}
@endif
</x-mail::panel>
@endforeach
@endif

@if($ongoingTasks->count() > 0)
## 🔄 Ongoing Tasks ({{ $ongoingTasks->count() }})

@foreach($ongoingTasks as $task)
<x-mail::panel>
**{{ $task->name }}**

- **Hours:** {{ $task->hours }}
- **Day:** {{ $task->day ?? 'Not specified' }}
- **Status:** Ongoing
@if($task->comment)
- **Comment:** {{ $task->comment }}
@endif
</x-mail::panel>
@endforeach
@endif

---

<x-mail::button :url="config('app.url') . '/calendar'">
View Your Calendar
</x-mail::button>

**Tips for staying productive:**
- Prioritize your pending tasks
- Update task status as you progress
- Complete ongoing tasks before starting new ones

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
