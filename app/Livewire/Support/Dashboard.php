<?php

namespace App\Livewire\Support;

use App\Interfaces\repositories\iissuelogInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;

    protected $issueRepository;

    public $filterStatus = '';
    public $filterPriority = '';
    public $search = '';
    
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
    }

    public function getAssignedIssues()
    {
        $issues = $this->issueRepository->getuserassignedissues(Auth::id());
        
        // Eager load comments relationship
        $issues->load('comments');
        
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