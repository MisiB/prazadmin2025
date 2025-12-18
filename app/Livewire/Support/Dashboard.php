<?php

namespace App\Livewire\Support;

use App\Interfaces\repositories\iissuelogInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;
use Carbon\Carbon;

class Dashboard extends Component
{
    use Toast;

    protected $issueRepository;

    public $filterStatus = '';
    public $filterPriority = '';
    public $search = '';
    
    // Export date range
    public $exportStartDate = '';
    public $exportEndDate = '';
    
    // Comments modal
    public $showCommentsModal = false;
    public $viewingIssueId = null;
    public $newComment = '';
    public $isInternalComment = false;
    
    // Comment editing
    public $editingCommentId = null;
    public $editCommentText = '';
    public $editIsInternal = false;

    public function boot(iissuelogInterface $issueRepository)
    {
        $this->issueRepository = $issueRepository;
    }

    public function mount()
    {
        // Check permission
        if (!Auth::user()->can('support.access')) {
            abort(403, 'Unauthorized access');
        }
        
        // Set default date range to last 30 days
        $this->exportStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->exportEndDate = Carbon::now()->format('Y-m-d');
    }

    public function getAssignedIssues()
    {
        $issues = $this->issueRepository->getuserassignedissues(Auth::id());
        
        // Eager load comments relationship
        $issues->load('comments', 'issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby', 'createdby');
        
        if ($this->search) {
            $issues = $issues->filter(function ($issue) {
                return str_contains(strtolower($issue->title), strtolower($this->search)) ||
                       str_contains(strtolower($issue->ticketnumber), strtolower($this->search)) ||
                       str_contains(strtolower($issue->description), strtolower($this->search));
            });
        }
        
        if ($this->filterStatus) {
            $issues = $issues->where('status', $this->filterStatus);
        }
        
        if ($this->filterPriority) {
            $issues = $issues->where('priority', $this->filterPriority);
        }
        
        return $issues;
    }

    public function getIssuesForExport()
    {
        $issues = $this->issueRepository->getuserassignedissues(Auth::id());
        
        // Eager load all relationships
        $issues->load('comments', 'issuegroup', 'issuetype', 'department', 'assignedto', 'assignedby', 'createdby');
        
        // Filter by date range
        if ($this->exportStartDate && $this->exportEndDate) {
            $startDate = Carbon::parse($this->exportStartDate)->startOfDay();
            $endDate = Carbon::parse($this->exportEndDate)->endOfDay();
            
            $issues = $issues->filter(function ($issue) use ($startDate, $endDate) {
                return $issue->created_at >= $startDate && $issue->created_at <= $endDate;
            });
        }
        
        // Apply other filters
        if ($this->filterStatus) {
            $issues = $issues->where('status', $this->filterStatus);
        }
        
        if ($this->filterPriority) {
            $issues = $issues->where('priority', $this->filterPriority);
        }
        
        if ($this->search) {
            $issues = $issues->filter(function ($issue) {
                return str_contains(strtolower($issue->title), strtolower($this->search)) ||
                       str_contains(strtolower($issue->ticketnumber), strtolower($this->search)) ||
                       str_contains(strtolower($issue->description), strtolower($this->search));
            });
        }
        
        return $issues;
    }

    public function exportToExcel()
    {
        $this->validate([
            'exportStartDate' => 'required|date',
            'exportEndDate' => 'required|date|after_or_equal:exportStartDate',
        ], [
            'exportStartDate.required' => 'Start date is required',
            'exportEndDate.required' => 'End date is required',
            'exportEndDate.after_or_equal' => 'End date must be after or equal to start date',
        ]);

        $issues = $this->getIssuesForExport();

        if ($issues->isEmpty()) {
            $this->error('No tickets found for the selected period.');
            return;
        }

        // Create CSV data
        $headers = [
            'Ticket Number',
            'Title',
            'Description',
            'Status',
            'Priority',
            'Group',
            'Type',
            'Reg Number',
            'Name',
            'Email',
            'Phone',
            'Department',
            'Assigned To',
            'Assigned By',
            'Assigned At',
            'Created By',
            'Created At',
            'Comments Count',
        ];

        $rows = [];
        $rows[] = $headers;

        foreach ($issues as $issue) {
            $comments = $issue->comments->map(function ($comment) {
                return $comment->user_email . ': ' . $comment->comment . ($comment->is_internal ? ' (Internal)' : '');
            })->implode(' | ');

            $rows[] = [
                $issue->ticketnumber ?? '',
                $issue->title ?? '',
                $issue->description ?? '',
                $issue->status ?? '',
                $issue->priority ?? '',
                $issue->issuegroup->name ?? 'N/A',
                $issue->issuetype->name ?? 'N/A',
                $issue->regnumber ?? '',
                $issue->name ?? '',
                $issue->email ?? '',
                $issue->phone ?? '',
                $issue->department->name ?? 'N/A',
                $issue->assignedto ? ($issue->assignedto->name . ' ' . $issue->assignedto->surname) : 'N/A',
                $issue->assignedby ? ($issue->assignedby->name . ' ' . $issue->assignedby->surname) : 'N/A',
                $issue->assigned_at ? $issue->assigned_at->format('Y-m-d H:i:s') : 'N/A',
                $issue->createdby ? ($issue->createdby->name . ' ' . $issue->createdby->surname) : 'N/A',
                $issue->created_at ? $issue->created_at->format('Y-m-d H:i:s') : '',
                $issue->comments->count(),
            ];
        }

        // Generate filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "support_tickets_export_{$timestamp}.csv";

        // Create a temporary file in storage path
        $tempPath = storage_path('app/public/' . $filename);

        // Ensure the directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Write to the file
        $file = fopen($tempPath, 'w');
        
        // Add BOM for UTF-8 to ensure Excel opens it correctly
        fwrite($file, "\xEF\xBB\xBF");
        
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        // Return download response
        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function updateStatus($id, $status)
    {
        $issue = $this->issueRepository->getissuelog($id);
        
        // Ensure user can only action issues assigned to them
        if (!$issue || (string)$issue->assigned_to !== (string)Auth::id()) {
            $this->error('You can only action issues assigned to you.');
            return;
        }
        
        $response = $this->issueRepository->updateissuestatus($id, $status);
        
        if ($response['status'] === 'success') {
            if ($status === 'resolved') {
                $this->success($response['message'].' Email notification sent to the user.');
            } else {
                $this->success($response['message']);
            }
        } else {
            $this->error($response['message']);
        }
    }

    public function openCommentsModal($issueId)
    {
        $issue = $this->issueRepository->getissuelog($issueId);
        
        // Ensure user can only view comments for issues assigned to them
        if (!$issue || (string)$issue->assigned_to !== (string)Auth::id()) {
            $this->error('You can only view comments for issues assigned to you.');
            return;
        }
        
        $this->viewingIssueId = $issueId;
        $this->newComment = '';
        $this->isInternalComment = false;
        $this->editingCommentId = null;
        $this->editCommentText = '';
        $this->editIsInternal = false;
        $this->showCommentsModal = true;
    }

    public function closeCommentsModal()
    {
        $this->showCommentsModal = false;
        $this->viewingIssueId = null;
        $this->newComment = '';
        $this->isInternalComment = false;
        $this->editingCommentId = null;
        $this->editCommentText = '';
        $this->editIsInternal = false;
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        $issue = $this->issueRepository->getissuelog($this->viewingIssueId);
        
        // Ensure user can only comment on issues assigned to them
        if (!$issue || (string)$issue->assigned_to !== (string)Auth::id()) {
            $this->error('You can only comment on issues assigned to you.');
            return;
        }
        
        $userEmail = Auth::user()->email ?? 'unknown@example.com';

        $response = $this->issueRepository->addcomment(
            $this->viewingIssueId,
            $userEmail,
            $this->newComment,
            $this->isInternalComment
        );

        if ($response['status'] === 'success') {
            $this->success($response['message']);
            $this->newComment = '';
            $this->isInternalComment = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function getIssueComments()
    {
        if (!$this->viewingIssueId) {
            return collect();
        }

        return $this->issueRepository->getissuecomments($this->viewingIssueId);
    }

    public function startEditComment($commentId)
    {
        $comment = $this->issueRepository->getcomment($commentId);

        // Only allow editing own comments
        if ($comment->user_email !== Auth::user()->email) {
            $this->error('You can only edit your own comments');
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editCommentText = $comment->comment;
        $this->editIsInternal = $comment->is_internal;
    }

    public function cancelEditComment()
    {
        $this->editingCommentId = null;
        $this->editCommentText = '';
        $this->editIsInternal = false;
    }

    public function updateComment()
    {
        $this->validate([
            'editCommentText' => 'required|string|min:1',
        ]);

        $comment = $this->issueRepository->getcomment($this->editingCommentId);

        // Only allow editing own comments
        if ($comment->user_email !== Auth::user()->email) {
            $this->error('You can only edit your own comments');
            return;
        }

        $response = $this->issueRepository->updatecomment(
            $this->editingCommentId,
            Auth::user()->email,
            $this->editCommentText,
            $this->editIsInternal
        );

        if ($response['status'] === 'success') {
            $this->success($response['message']);
            $this->cancelEditComment();
        } else {
            $this->error($response['message']);
        }
    }

    public function deleteComment($commentId)
    {
        $comment = $this->issueRepository->getcomment($commentId);

        // Only allow deleting own comments
        if ($comment->user_email !== Auth::user()->email) {
            $this->error('You can only delete your own comments');
            return;
        }

        $response = $this->issueRepository->deletecomment($commentId);

        if ($response['status'] === 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function getStatusCounts()
    {
        $issues = $this->getAssignedIssues();
        
        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
    }

    #[Layout('components.layouts.support')]
    public function render()
    {
        $assignedIssues = $this->getAssignedIssues();
        $statusCounts = $this->getStatusCounts();

        return view('livewire.support.dashboard', [
            'issues' => $assignedIssues,
            'statusCounts' => $statusCounts,
        ]);
    }
}