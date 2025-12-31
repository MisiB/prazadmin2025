<?php

namespace App\Livewire\Admin\Workflows;

use Livewire\Component;

class TsAllowance extends Component
{
    public $breadcrumbs = [];

    public $uuid;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowances', 'link' => route('admin.workflows.ts-allowances')],
            ['label' => 'T&S Allowance'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.ts-allowance', [
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }
}
