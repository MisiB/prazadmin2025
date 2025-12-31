<?php

namespace App\Livewire\Admin\Workflows;

use Livewire\Component;

class StaffWelfareLoan extends Component
{
    public $breadcrumbs = [];

    public $uuid;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loans', 'link' => route('admin.workflows.staff-welfare-loans')],
            ['label' => 'Staff Welfare Loan'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.staff-welfare-loan', [
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }
}
