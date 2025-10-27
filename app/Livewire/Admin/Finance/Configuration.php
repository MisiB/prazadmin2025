<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;

class Configuration extends Component
{
    public $breadcrumbs = [];
    public $selectedTab = 'currency-tab';
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Finance Configuration']
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.configuration');
    }
}
