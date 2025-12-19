<?php

namespace App\implementation\services;

use App\Interfaces\services\iissueService;
use App\Models\Issuelog;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\IssueCommentNotification;
use App\Notifications\IssuePriorityChangedNotification;
use App\Notifications\IssueResolvedNotification;
use App\Notifications\IssueStatusChangedNotification;
use App\Notifications\NewticketNotification;
use Illuminate\Support\Facades\Notification;

class _issueService implements iissueService
{
    /**
     * Check if user has permission to view issues
     */
    protected function canViewIssues(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        // Check for common issue-related permissions
        $permissions = [
            'view issues',
            'manage issues',
            'issues.view',
            'issues.manage',
            'view_issues',
            'manage_issues',
        ];

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        // If no specific permission found, allow if user is assigned (they need to know)
        return true;
    }

    public function notifyIssueCreated(Issuelog $issue): void
    {
        // Always notify issue creator - no permission check needed
        if ($issue->email) {
            Notification::route('mail', $issue->email)
                ->notify(new NewticketNotification($issue->name, $issue->ticketnumber, $issue->description));
        }
    }

    public function notifyIssueAssigned(Issuelog $issue, int $assignedUserId): void
    {
        $assignedUser = $issue->assignedto;

        // Always notify assigned user immediately - no permission check needed
        if ($assignedUser && $assignedUser->email) {
            $assignedUser->notify(new IssueAssignedNotification($issue));
        }
    }

    public function notifyIssueStatusChanged(Issuelog $issue, string $oldStatus, string $newStatus): void
    {
        // Special handling for resolved status - always notify issue creator
        if ($newStatus === 'resolved' && $issue->email) {
            Notification::route('mail', $issue->email)
                ->notify(new IssueResolvedNotification($issue));

            return;
        }

        // Notify issue creator on other status changes - no permission check needed
        if ($oldStatus !== $newStatus && $issue->email) {
            Notification::route('mail', $issue->email)
                ->notify(new IssueStatusChangedNotification($issue, $oldStatus, $newStatus));
        }

        // Notify assigned user if status changes to in_progress (check permission)
        if ($newStatus === 'in_progress' && $issue->assigned_to) {
            $assignedUser = $issue->assignedto;
            if ($assignedUser && $assignedUser->email && $this->canViewIssues($assignedUser)) {
                $assignedUser->notify(new IssueStatusChangedNotification($issue, $oldStatus, $newStatus));
            }
        }
    }

    public function notifyIssuePriorityChanged(Issuelog $issue, string $oldPriority, string $newPriority): void
    {
        if ($oldPriority !== $newPriority) {
            // Notify issue creator - no permission check needed
            if ($issue->email) {
                Notification::route('mail', $issue->email)
                    ->notify(new IssuePriorityChangedNotification($issue, $oldPriority, $newPriority));
            }

            // Notify assigned user if exists (check permission)
            if ($issue->assigned_to) {
                $assignedUser = $issue->assignedto;
                if ($assignedUser && $assignedUser->email && $this->canViewIssues($assignedUser)) {
                    $assignedUser->notify(new IssuePriorityChangedNotification($issue, $oldPriority, $newPriority));
                }
            }
        }
    }

    public function notifyIssueCommentAdded(Issuelog $issue, \App\Models\Issuecomment $comment, bool $isInternal): void
    {
        // Only notify if it's not an internal comment - always notify issue creator
        if (! $isInternal && $issue->email) {
            Notification::route('mail', $issue->email)
                ->notify(new IssueCommentNotification($comment, $issue));
        }
    }
}
