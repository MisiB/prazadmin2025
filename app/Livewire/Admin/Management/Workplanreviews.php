<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\istrategyInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Workplanreviews extends Component
{
    use Toast;

    protected $individualworkplanrepo;

    protected $strategyrepo;

    protected $departmentrepo;

    public $year;

    public $strategy_id;

    public $modal = false;

    public $workplans;

    public $breadcrumbs = [];

    public $selectedWorkplan = null;

    public $reviewModal = false;

    public $workplanId;

    public $headers;

    public $remarks;

    public $activeTab = 'pending'; // Add this property

    public $approvedWorkplans; // Add this property

    public function boot(
        individualworkplanInterface $individualworkplanrepo,
        istrategyInterface $strategyrepo,
        idepartmentInterface $departmentrepo
    ) {
        $this->individualworkplanrepo = $individualworkplanrepo;
        $this->strategyrepo = $strategyrepo;
        $this->departmentrepo = $departmentrepo;
    }

    public function mount()
    {
        $this->year = Carbon::now()->year;
        $this->workplans = new Collection;
        $this->approvedWorkplans = new Collection(); // Add this
        $this->activeTab = 'pending'; // Add this
        $this->breadcrumbs = [
            [
                'label' => 'Home',
                'link' => route('admin.home'),
            ],
            [
                'label' => 'Workplan Reviews',
            ],
        ];
        $this->headers = $this->headers();
    }

    public function getstrategies()
    {
        return $this->strategyrepo->getstrategies();
    }

    public function getworkplans()
    {
        $this->validate([
            'strategy_id' => 'required',
            'year' => 'required',
        ]);

        $workplans = $this->individualworkplanrepo->getsubordinatesworkplans(
            Auth::user()->id,
            $this->strategy_id,
            $this->year
        );

        $this->workplans = collect($workplans);
        
        // Also fetch approved workplans
        $department_id = Auth::user()->department->department_id;
        $approvedWorkplans = $this->individualworkplanrepo->getapprovedworkplansbydepartment(
            $department_id,
            $this->strategy_id,
            $this->year
        );
        $this->approvedWorkplans = collect($approvedWorkplans);
        
        $this->modal = false;
    }

    public function getapprovedworkplans()
    {
        $this->validate([
            'strategy_id' => 'required',
            'year' => 'required',
        ]);

        $department_id = Auth::user()->department->department_id;
        
        $approvedWorkplans = $this->individualworkplanrepo->getapprovedworkplansbydepartment(
            $department_id,
            $this->strategy_id,
            $this->year
        );

        $this->approvedWorkplans = collect($approvedWorkplans);
        $this->modal = false;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'approved' && $this->strategy_id && $this->year) {
            $this->getapprovedworkplans();
        }
    }

    public function reviewworkplan($id)
    {
        $this->workplanId = $id;
        
        // Try to find in pending workplans first
        $this->selectedWorkplan = $this->workplans->where('id', $id)->first();
        
        // If not found, try approved workplans
        if (!$this->selectedWorkplan) {
            $this->selectedWorkplan = $this->approvedWorkplans->where('id', $id)->first();
        }
        
        // If still not found, load directly from repository
        if (!$this->selectedWorkplan) {
            $workplan = $this->individualworkplanrepo->getindividualworkplan($id);
            if ($workplan) {
                $workplan->load('user', 'targetmatrix.target.indicator.departmentoutput.output.outcome.programme');
                $this->selectedWorkplan = $workplan;
            }
        }
        
        $this->remarks = '';
        $this->reviewModal = true;
    }

    public function approveworkplan($id)
    {
        $response = $this->individualworkplanrepo->approveindividualworkplan($id, $this->remarks);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reviewModal = false;
            $this->remarks = '';
            $this->getworkplans();
        } else {
            $this->error($response['message']);
        }
    }

    public function closeModal()
    {
        $this->modal = false;
    }

    public function closeReviewModal()
    {
        $this->reviewModal = false;
        $this->selectedWorkplan = null;
        $this->workplanId = null;
        $this->remarks = '';
    }

    public function headers(): array
    {
        return [
            ['key' => 'user.name', 'label' => 'Employee'],
            ['key' => 'output', 'label' => 'Output', 'class' => 'w-64'],
            ['key' => 'indicator', 'label' => 'Indicator', 'class' => 'w-64'],
            ['key' => 'target', 'label' => 'Target'],
            ['key' => 'month', 'label' => 'Month'],
            ['key' => 'weightage', 'label' => 'Weightage %'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'actions', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.management.workplanreviews', [
            'strategies' => $this->getstrategies(),
            'headers' => $this->headers(),
        ]);
    }
}
