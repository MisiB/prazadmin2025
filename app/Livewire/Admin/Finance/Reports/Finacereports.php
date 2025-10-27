<?php

namespace App\Livewire\Admin\Finance\Reports;

use Livewire\Component;

class Finacereports extends Component
{
    public $breadcrumbs = [];
    public $selectedTab = 'revenue-tab';
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Finance Reports']
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.reports.finacereports'); 
    }
}
