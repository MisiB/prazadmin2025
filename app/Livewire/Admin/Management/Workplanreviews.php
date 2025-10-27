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
        $this->modal = false;
    }

    public function reviewworkplan($id)
    {
        $this->workplanId = $id;
        $this->selectedWorkplan = $this->workplans->where('id', $id)->first();
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
