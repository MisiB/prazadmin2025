<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuelogInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Issues extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public $breadcrumbs = [];

    // Form fields
    public $issuegroup_id;

    public $issuetype_id;

    public $regnumber;

    public $name;

    public $email;

    public $phone;

    public $title;

    public $description;

    public $priority = 'Medium';

    public $status = 'open';

    // Component state
    public $showModal = false;

    public $editingId = null;

    public $filterStatus = '';

    public $filterPriority = '';

    public $search = '';

    public $attachments = [];

    public $existingAttachments = [];

    // Assignment modal
    public $showAssignModal = false;

    public $assigningIssueId = null;

    public $selectedDepartment = null;

    public $selectedUser = null;

    // Comments
    public $showCommentsModal = false;

    public $viewingIssueId = null;

    public $newComment = '';

    public $isInternalComment = false;

    public $editingCommentId = null;

    public $editCommentText = '';

    public $editIsInternal = false;

    protected $issueRepository;

    protected $departmentRepository;

    public function boot(iissuelogInterface $issueRepository, idepartmentInterface $departmentRepository)
    {
        $this->issueRepository = $issueRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Issue Tickets'],
        ];

        // Pre-fill user data
        $user = Auth::user();
        $this->name = $user->name.' '.$user->surname;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $user = Auth::user();

        $this->reset([
            'editingId',
            'issuegroup_id',
            'issuetype_id',
            'regnumber',
            'title',
            'description',
            'priority',
            'attachments',
            'existingAttachments',
        ]);

        $this->name = $user->name.' '.$user->surname;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->priority = 'Medium';

        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'issuegroup_id' => 'required|exists:issuegroups,id',
            'issuetype_id' => 'required|exists:issuetypes,id',
            'regnumber' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'attachments.*' => 'nullable|image|max:5120', // Max 5MB per image
        ]);

        // Handle file uploads
        $attachmentPaths = $this->existingAttachments;

        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $path = $file->store('issue-attachments', 'public');
                $attachmentPaths[] = $path;
            }
        }

        $data = [
            'issuegroup_id' => $this->issuegroup_id,
            'issuetype_id' => $this->issuetype_id,
            'regnumber' => $this->regnumber,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'attachments' => $attachmentPaths,
        ];

        if ($this->editingId) {
            $response = $this->issueRepository->updateissuelog($this->editingId, $data);
        } else {
            $data['status'] = 'open';
            $response = $this->issueRepository->createissuelog($data);
        }

        if ($response['status'] === 'success') {
            $this->success($response['message']);
            $this->closeModal();
        } else {
            $this->error($response['message']);
        }
    }

    public function removeAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            // Delete file from storage
            Storage::disk('public')->delete($this->existingAttachments[$index]);
            unset($this->existingAttachments[$index]);
            $this->existingAttachments = array_values($this->existingAttachments);
        }
    }

    public function edit($id)
    {
        $issue = $this->issueRepository->getissuelog($id);

        $this->editingId = $issue->id;
        $this->issuegroup_id = $issue->issuegroup_id;
        $this->issuetype_id = $issue->issuetype_id;
        $this->regnumber = $issue->regnumber;
        $this->name = $issue->name;
        $this->email = $issue->email;
        $this->phone = $issue->phone;
        $this->title = $issue->title;
        $this->description = $issue->description;
        $this->priority = $issue->priority;
        $this->status = $issue->status;
        $this->existingAttachments = $issue->attachments ?? [];

        $this->showModal = true;
    }

    public function delete($id)
    {
        $response = $this->issueRepository->deleteissuelog($id);

        if ($response['status'] === 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function updateStatus($id, $status)
    {
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

    public function openAssignModal($issueId)
    {
        $this->assigningIssueId = $issueId;
        $this->selectedDepartment = null;
        $this->selectedUser = null;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assigningIssueId = null;
        $this->selectedDepartment = null;
        $this->selectedUser = null;
    }

    public function assignIssue()
    {
        $this->validate([
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedUser' => 'required|exists:users,id',
        ]);

        $response = $this->issueRepository->assignissue(
            $this->assigningIssueId,
            $this->selectedUser,
            $this->selectedDepartment
        );

        if ($response['status'] === 'success') {
            $this->success($response['message']);
            $this->closeAssignModal();
        } else {
            $this->error($response['message']);
        }
    }

    public function getDepartmentUsers()
    {
        if (! $this->selectedDepartment) {
            return collect();
        }

        return $this->issueRepository->getdepartmentusers($this->selectedDepartment);
    }

    public function getIssues()
    {
        $query = $this->issueRepository->getissuelogs();

        if ($this->search) {
            $query = $query->filter(function ($issue) {
                return str_contains(strtolower($issue->title), strtolower($this->search)) ||
                       str_contains(strtolower($issue->ticketnumber), strtolower($this->search)) ||
                       str_contains(strtolower($issue->description), strtolower($this->search));
            });
        }

        if ($this->filterStatus) {
            $query = $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query = $query->where('priority', $this->filterPriority);
        }

        return $query;
    }

    public function getDepartments()
    {
        return $this->departmentRepository->getdepartments();
    }

    public function openCommentsModal($issueId)
    {
        $this->viewingIssueId = $issueId;
        $this->newComment = '';
        $this->isInternalComment = false;
        $this->showCommentsModal = true;
    }

    public function closeCommentsModal()
    {
        $this->showCommentsModal = false;
        $this->viewingIssueId = null;
        $this->newComment = '';
        $this->isInternalComment = false;
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        $userEmail = auth()->user()->email ?? 'unknown@example.com';

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
        if (! $this->viewingIssueId) {
            return collect();
        }

        return $this->issueRepository->getissuecomments($this->viewingIssueId);
    }

    public function startEditComment($commentId)
    {
        $comment = $this->issueRepository->getcomment($commentId);

        // Only allow editing own comments
        if ($comment->user_email !== auth()->user()->email) {
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
        if ($comment->user_email !== auth()->user()->email) {
            $this->error('You can only edit your own comments');

            return;
        }

        $response = $this->issueRepository->updatecomment(
            $this->editingCommentId,
            auth()->user()->email,
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
        if ($comment->user_email !== auth()->user()->email) {
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

    public function render()
    {
        return view('livewire.admin.issues', [
            'issues' => $this->getIssues(),
            'issuegroups' => $this->issueRepository->getissuegroups(),
            'issuetypes' => $this->issueRepository->getissuetypes(),
            'departments' => $this->getDepartments(),
            'departmentUsers' => $this->getDepartmentUsers(),
            'issueComments' => $this->getIssueComments(),
        ]);
    }
}
