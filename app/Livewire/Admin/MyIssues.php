<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\iissuelogInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class MyIssues extends Component
{
    use Toast, WithFileUploads;

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

    // Component state
    public $showModal = false;

    public $editingId = null;

    public $activeTab = 'created';

    public $filterStatus = '';

    public $filterPriority = '';

    public $search = '';

    public $attachments = [];

    public $existingAttachments = [];

    protected $issueRepository;

    public function boot(iissuelogInterface $issueRepository)
    {
        $this->issueRepository = $issueRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'My Issues'],
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
            'attachments.*' => 'nullable|image|max:5120',
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
        $this->existingAttachments = $issue->attachments ?? [];

        $this->showModal = true;
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

    public function getCreatedIssues()
    {
        $query = $this->issueRepository->getusercreatedissues(Auth::id());

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

    public function getAssignedIssues()
    {
        $query = $this->issueRepository->getuserassignedissues(Auth::id());

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

    public function getStatusCounts($issues)
    {
        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
    }

    public function render()
    {
        $createdIssues = $this->getCreatedIssues();
        $assignedIssues = $this->getAssignedIssues();

        return view('livewire.admin.my-issues', [
            'createdIssues' => $createdIssues,
            'assignedIssues' => $assignedIssues,
            'createdCounts' => $this->getStatusCounts($createdIssues),
            'assignedCounts' => $this->getStatusCounts($assignedIssues),
            'issuegroups' => $this->issueRepository->getissuegroups(),
            'issuetypes' => $this->issueRepository->getissuetypes(),
        ]);
    }
}
