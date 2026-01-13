<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\icalendarInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\ileaverequestService;
use App\Mail\SupervisorTaskReminderMail;
use App\Models\Calenderworkusertask;
use App\Models\Departmentuser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendSupervisorTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-supervisor-reminders {--supervisor-id= : Send to specific supervisor only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send comprehensive email reminders to supervisors about tasks needing their review (completed tasks and submitted calendar weeks)';

    protected $taskRepository;

    protected $calendarRepository;

    protected $leaverequestService;

    public function __construct(
        itaskInterface $taskRepository,
        icalendarInterface $calendarRepository,
        ileaverequestService $leaverequestService
    ) {
        parent::__construct();
        $this->taskRepository = $taskRepository;
        $this->calendarRepository = $calendarRepository;
        $this->leaverequestService = $leaverequestService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting supervisor task reminder process...');

        // Get all completed tasks awaiting approval
        $completedTasks = Task::with(['user', 'calendarday'])
            ->where('status', 'completed')
            ->where('approvalstatus', 'pending')
            ->get();

        // Get all calendar weeks submitted for approval
        $submittedWeeks = Calenderworkusertask::with(['user', 'calendarweek.calendardays.tasks'])
            ->where('status', 'pending')
            ->get();

        // Group tasks and weeks by supervisor
        $supervisorData = $this->groupBySupervisor($completedTasks, $submittedWeeks);

        if ($supervisorData->isEmpty()) {
            $this->warn('No supervisors found with tasks needing review.');

            return self::SUCCESS;
        }

        // Filter by specific supervisor if provided
        if ($this->option('supervisor-id')) {
            $supervisorData = $supervisorData->filter(function ($data, $supervisorId) {
                return $supervisorId === $this->option('supervisor-id');
            });
        }

        $emailsSent = 0;
        $supervisorsWithNoTasks = 0;

        foreach ($supervisorData as $supervisorId => $data) {
            $supervisor = User::find($supervisorId);

            if (! $supervisor || ! $supervisor->email) {
                $this->warn("Supervisor {$supervisorId} not found or has no email address.");

                continue;
            }

            $completedTasksCount = $data['completedTasks']->count();
            $submittedWeeksCount = $data['submittedWeeks']->count();

            if ($completedTasksCount === 0 && $submittedWeeksCount === 0) {
                $supervisorsWithNoTasks++;

                continue;
            }

            // Send email
            try {
                Mail::to($supervisor->email)->send(
                    new SupervisorTaskReminderMail(
                        $supervisor,
                        $data['completedTasks'],
                        $data['submittedWeeks']
                    )
                );

                $emailsSent++;
                $this->info("✓ Sent reminder to {$supervisor->name} ({$supervisor->email}) - {$completedTasksCount} completed task(s), {$submittedWeeksCount} submitted week(s)");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminder to {$supervisor->email}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total supervisors processed: {$supervisorData->count()}");
        $this->info("Emails sent: {$emailsSent}");
        $this->info("Supervisors with no tasks: {$supervisorsWithNoTasks}");

        return self::SUCCESS;
    }

    /**
     * Group tasks and submitted weeks by supervisor
     */
    protected function groupBySupervisor(Collection $completedTasks, Collection $submittedWeeks): Collection
    {
        $supervisorData = collect();

        // Process completed tasks
        foreach ($completedTasks as $task) {
            $supervisorId = $this->getNotificationRecipient($task->user_id);

            if (! $supervisorId) {
                continue;
            }

            if (! $supervisorData->has($supervisorId)) {
                $supervisorData[$supervisorId] = [
                    'completedTasks' => collect(),
                    'submittedWeeks' => collect(),
                ];
            }

            $supervisorData[$supervisorId]['completedTasks']->push($task);
        }

        // Process submitted calendar weeks
        foreach ($submittedWeeks as $submittedWeek) {
            $supervisorId = $this->getNotificationRecipient($submittedWeek->user_id);

            if (! $supervisorId) {
                continue;
            }

            if (! $supervisorData->has($supervisorId)) {
                $supervisorData[$supervisorId] = [
                    'completedTasks' => collect(),
                    'submittedWeeks' => collect(),
                ];
            }

            $supervisorData[$supervisorId]['submittedWeeks']->push($submittedWeek);
        }

        return $supervisorData;
    }

    /**
     * Get the correct supervisor to notify based on leave status chain
     * Priority: Direct Supervisor → Supervisor's Acting → HOD → HOD's Acting
     */
    protected function getNotificationRecipient(string $userId): ?string
    {
        $userDepartment = Departmentuser::where('user_id', $userId)->first();

        if (! $userDepartment || ! $userDepartment->reportto) {
            return null;
        }

        $supervisorId = $userDepartment->reportto;

        // Check if direct supervisor is on leave
        $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);

        if ($supervisorLeaveStatus['status'] !== true) {
            // Supervisor is available - send to them
            return $supervisorId;
        }

        // Supervisor is on leave - check if they assigned an acting person
        if (isset($supervisorLeaveStatus['actinghodid']) && $supervisorLeaveStatus['actinghodid']) {
            return $supervisorLeaveStatus['actinghodid'];
        }

        // No acting assigned by supervisor - check HOD
        $hod = Departmentuser::where('department_id', $userDepartment->department_id)
            ->where('isprimary', true)
            ->first();

        if ($hod) {
            $hodLeaveStatus = $this->leaverequestService->isactiveonleave($hod->user_id);

            if ($hodLeaveStatus['status'] !== true) {
                // HOD is available
                return $hod->user_id;
            } else {
                // HOD is on leave - use their acting member
                if (isset($hodLeaveStatus['actinghodid']) && $hodLeaveStatus['actinghodid']) {
                    return $hodLeaveStatus['actinghodid'];
                }
            }
        }

        // Fallback to direct supervisor (even if on leave, still notify)
        return $supervisorId;
    }
}
