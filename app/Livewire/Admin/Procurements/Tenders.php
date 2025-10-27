<?php

namespace App\Livewire\Admin\Procurements;

use Livewire\Component;

class Tenders extends Component
{
    public $breadcrumbs = [];
    public $selectedTab = 'tenders-tab';
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Tender management']
        ];
    }
    public function render()
    {
        return view('livewire.admin.procurements.tenders');
    }
}
