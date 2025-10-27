<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iissuelogInterface;
use App\Models\Departmentuser;
use App\Models\Issuecomment;
use App\Models\Issuegroup;
use App\Models\Issuelog;
use App\Models\Issuetype;
use App\Notifications\IssueCommentNotification;
use App\Notifications\IssueResolvedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class _issuelogRepository implements iissuelogInterface
{
    protected $issuelog;

    protected $issuegroup;

    protected $issuetype;

    public function __construct(Issuelog $issuelog, Issuegroup $issuegroup, Issuetype $issuetype)
    {
        $this->issuelog = $issuelog;
        $this->issuegroup = $issuegroup;
        $this->issuetype = $issuetype;
    }

    public function getissuelogsbyemail($email)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby'])
            ->where('email', $email)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getissuelogs()
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getissuelog($id)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby', 'comments'])
            ->where('id', $id)
            ->first();
    }

    public function createissuelog(array $data): array
    {
        try {
            // Generate ticket number
            $ticketNumber = 'TKT-'.strtoupper(Str::random(8));
            $data['ticketnumber'] = $ticketNumber;
            $data['status'] = $data['status'] ?? 'open';
            $data['created_by'] = auth()->id();

            // Handle attachments if present
            if (! empty($data['attachments']) && is_array($data['attachments'])) {
                $storedAttachments = [];

                foreach ($data['attachments'] as $attachment) {
                    if (! empty($attachment['data']) && ! empty($attachment['name'])) {
                        // Decode base64 data
                        $fileData = base64_decode($attachment['data']);

                        if ($fileData === false) {
                            continue; // Skip invalid base64 data
                        }

                        // Generate unique filename
                        $extension = pathinfo($attachment['name'], PATHINFO_EXTENSION);
                        $filename = Str::random(40).'.'.$extension;

                        // Store file in public disk under issue-attachments/{ticketnumber}
                        $path = "issue-attachments/{$ticketNumber}/{$filename}";
                        Storage::disk('public')->put($path, $fileData);

                        // Save file metadata
                        $storedAttachments[] = [
                            'original_name' => $attachment['name'],
                            'filename' => $filename,
                            'path' => $path,
                            'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                            'size' => strlen($fileData),
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                    }
                }

                $data['attachments'] = $storedAttachments;
            } else {
                $data['attachments'] = null;
            }

            $issue = $this->issuelog->create($data);

            return [
                'status' => 'success',
                'message' => 'Issue ticket created successfully',
                'data' => [
                    'id' => $issue->id,
                    'ticketnumber' => $issue->ticketnumber,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to create issue: '.$e->getMessage(),
            ];
        }
    }

    public function updateissuelog($id, array $data): array
    {
        try {
            $issue = $this->issuelog->findOrFail($id);
            $issue->update($data);

            return [
                'status' => 'success',
                'message' => 'Issue updated successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update issue: '.$e->getMessage(),
            ];
        }
    }

    public function deleteissuelog($id): array
    {
        try {
            $issue = $this->issuelog->findOrFail($id);
            $issue->delete();

            return [
                'status' => 'success',
                'message' => 'Issue deleted successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete issue: '.$e->getMessage(),
            ];
        }
    }

    public function getissuegroups()
    {
        return $this->issuegroup->orderBy('name', 'asc')->get();
    }

    public function getissuetypes()
    {
        return $this->issuetype->orderBy('name', 'asc')->get();
    }

    public function getissuetypesbygroup($issuegroupId)
    {
        // Issue types are not directly linked to groups in the schema
        // Return all issue types for now
        return $this->getissuetypes();
    }

    public function getuserissues($userId)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby'])
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getusercreatedissues($userId)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby'])
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getuserassignedissues($userId)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby'])
            ->where('assigned_to', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateissuestatus($id, $status): array
    {
        try {
            $issue = $this->issuelog->findOrFail($id);
            $issue->update(['status' => $status]);

            // Send email notification when issue is resolved
            if ($status === 'resolved' && $issue->email) {
                Notification::route('mail', $issue->email)
                    ->notify(new IssueResolvedNotification($issue));
            }

            return [
                'status' => 'success',
                'message' => 'Issue status updated successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update status: '.$e->getMessage(),
            ];
        }
    }

    public function assignissue($id, $userId, $departmentId): array
    {
        try {
            $issue = $this->issuelog->findOrFail($id);

            $issue->update([
                'assigned_to' => $userId,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'department_id' => $departmentId,
            ]);

            return [
                'status' => 'success',
                'message' => 'Issue assigned successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to assign issue: '.$e->getMessage(),
            ];
        }
    }

    public function getdepartmentissues($departmentId)
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'assignedto', 'assignedby'])
            ->where('department_id', $departmentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getdepartmentusers($departmentId)
    {
        return Departmentuser::where('department_id', $departmentId)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();
    }

    public function getissueswithmetrics()
    {
        return $this->issuelog
            ->with(['issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby', 'createdby'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getdepartmentturnaroundtime($departmentId)
    {
        $issues = $this->issuelog
            ->where('department_id', $departmentId)
            ->whereIn('status', ['resolved', 'closed'])
            ->get();

        if ($issues->isEmpty()) {
            return [
                'avg_hours' => 0,
                'total_issues' => 0,
            ];
        }

        $totalHours = 0;
        $count = 0;

        foreach ($issues as $issue) {
            if ($issue->status === 'resolved' || $issue->status === 'closed') {
                $resolvedAt = $issue->updated_at;
                $createdAt = $issue->created_at;
                $hours = $createdAt->diffInHours($resolvedAt);
                $totalHours += $hours;
                $count++;
            }
        }

        return [
            'avg_hours' => $count > 0 ? round($totalHours / $count, 2) : 0,
            'total_issues' => $count,
        ];
    }

    public function getuserturnaroundtime($userId)
    {
        $issues = $this->issuelog
            ->where('assigned_to', $userId)
            ->whereIn('status', ['resolved', 'closed'])
            ->get();

        if ($issues->isEmpty()) {
            return [
                'avg_hours' => 0,
                'total_issues' => 0,
            ];
        }

        $totalHours = 0;
        $count = 0;

        foreach ($issues as $issue) {
            if ($issue->assigned_at) {
                $resolvedAt = $issue->updated_at;
                $assignedAt = $issue->assigned_at;
                $hours = $assignedAt->diffInHours($resolvedAt);
                $totalHours += $hours;
                $count++;
            }
        }

        return [
            'avg_hours' => $count > 0 ? round($totalHours / $count, 2) : 0,
            'total_issues' => $count,
        ];
    }

    public function addcomment($issueId, $userEmail, $comment, $isInternal = false): array
    {
        try {
            $issue = $this->issuelog->findOrFail($issueId);

            $commentRecord = Issuecomment::create([
                'issuelog_id' => $issueId,
                'user_email' => $userEmail,
                'comment' => $comment,
                'is_internal' => $isInternal,
            ]);

            // Send email notification to the issue creator if it's not an internal comment
            if (! $isInternal && $issue->email) {
                Notification::route('mail', $issue->email)
                    ->notify(new IssueCommentNotification($commentRecord, $issue));
            }

            return [
                'status' => 'success',
                'message' => 'Comment added successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to add comment: '.$e->getMessage(),
            ];
        }
    }

    public function getissuecomments($issueId)
    {
        return Issuecomment::where('issuelog_id', $issueId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getcomment($commentId)
    {
        return Issuecomment::with('issuelog')
            ->findOrFail($commentId);
    }

    public function updatecomment($commentId, $userEmail, $comment, $isInternal = false): array
    {
        try {
            $commentRecord = Issuecomment::findOrFail($commentId);

            $commentRecord->update([
                'comment' => $comment,
                'is_internal' => $isInternal,
                'user_email' => $userEmail,
            ]);

            return [
                'status' => 'success',
                'message' => 'Comment updated successfully',
                'data' => $commentRecord,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update comment: '.$e->getMessage(),
            ];
        }
    }

    public function deletecomment($commentId): array
    {
        try {
            $comment = Issuecomment::findOrFail($commentId);
            $comment->delete();

            return [
                'status' => 'success',
                'message' => 'Comment deleted successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete comment: '.$e->getMessage(),
            ];
        }
    }
}
