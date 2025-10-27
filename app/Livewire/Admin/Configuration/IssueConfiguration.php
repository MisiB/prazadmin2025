<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuegroupInterface;
use App\Interfaces\repositories\iissuetypeInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class IssueConfiguration extends Component
{
    use Toast;

    public $breadcrumbs = [];

    // Issue Group Form
    public $showGroupModal = false;

    public $groupId = null;

    public $groupName = '';

    // Issue Type Form
    public $showTypeModal = false;

    public $typeId = null;

    public $typeName = '';

    public $typeDepartmentId = null;

    public $activeTab = 'groups';

    protected $departmentRepository;

    protected $issueGroupRepository;

    protected $issueTypeRepository;

    public function boot(
        idepartmentInterface $departmentRepository,
        iissuegroupInterface $issueGroupRepository,
        iissuetypeInterface $issueTypeRepository
    ) {
        $this->departmentRepository = $departmentRepository;
        $this->issueGroupRepository = $issueGroupRepository;
        $this->issueTypeRepository = $issueTypeRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Configuration', 'link' => '#'],
            ['label' => 'Issue Management'],
        ];
    }

    // Issue Group Methods
    public function openGroupModal()
    {
        $this->resetGroupForm();
        $this->showGroupModal = true;
    }

    public function closeGroupModal()
    {
        $this->showGroupModal = false;
        $this->resetGroupForm();
    }

    public function resetGroupForm()
    {
        $this->reset(['groupId', 'groupName']);
        $this->resetValidation();
    }

    public function saveGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255',
        ]);

        $data = ['name' => $this->groupName];

        if ($this->groupId) {
            $result = $this->issueGroupRepository->update($this->groupId, $data);
        } else {
            $result = $this->issueGroupRepository->create($data);
        }

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->closeGroupModal();
        } else {
            $this->error($result['message']);
        }
    }

    public function editGroup($id)
    {
        $group = $this->issueGroupRepository->getIssueGroup($id);
        $this->groupId = $group->id;
        $this->groupName = $group->name;
        $this->showGroupModal = true;
    }

    public function deleteGroup($id)
    {
        $result = $this->issueGroupRepository->delete($id);

        if ($result['status'] === 'success') {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    // Issue Type Methods
    public function openTypeModal()
    {
        $this->resetTypeForm();
        $this->showTypeModal = true;
    }

    public function closeTypeModal()
    {
        $this->showTypeModal = false;
        $this->resetTypeForm();
    }

    public function resetTypeForm()
    {
        $this->reset(['typeId', 'typeName', 'typeDepartmentId']);
        $this->resetValidation();
    }

    public function saveType()
    {
        $this->validate([
            'typeName' => 'required|string|max:255',
            'typeDepartmentId' => 'nullable|exists:departments,id',
        ]);

        $data = [
            'name' => $this->typeName,
            'department_id' => $this->typeDepartmentId,
        ];

        if ($this->typeId) {
            $result = $this->issueTypeRepository->update($this->typeId, $data);
        } else {
            $result = $this->issueTypeRepository->create($data);
        }

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->closeTypeModal();
        } else {
            $this->error($result['message']);
        }
    }

    public function editType($id)
    {
        $type = $this->issueTypeRepository->getIssueType($id);
        $this->typeId = $type->id;
        $this->typeName = $type->name;
        $this->typeDepartmentId = $type->department_id;
        $this->showTypeModal = true;
    }

    public function deleteType($id)
    {
        $result = $this->issueTypeRepository->delete($id);

        if ($result['status'] === 'success') {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.configuration.issue-configuration', [
            'issueGroups' => $this->issueGroupRepository->getIssueGroups(),
            'issueTypes' => $this->issueTypeRepository->getIssueTypes(),
            'departments' => $this->departmentRepository->getdepartments(),
        ]);
    }
}
