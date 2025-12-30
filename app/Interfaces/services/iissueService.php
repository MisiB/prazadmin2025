<?php

namespace App\Interfaces\services;

use App\Models\Issuelog;

interface iissueService
{
    public function notifyIssueCreated(Issuelog $issue): void;

    //assignedUserId cannot be int because the userid is stored as uuid
    public function notifyIssueAssigned(Issuelog $issue, $assignedUserId): void;

    public function notifyIssueStatusChanged(Issuelog $issue, string $oldStatus, string $newStatus): void;

    public function notifyIssuePriorityChanged(Issuelog $issue, string $oldPriority, string $newPriority): void;

    public function notifyIssueCommentAdded(Issuelog $issue, \App\Models\Issuecomment $comment, bool $isInternal): void;
}
