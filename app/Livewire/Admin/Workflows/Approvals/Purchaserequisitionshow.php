<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use Livewire\Component;

class Purchaserequisitionshow extends Component
{
    public $uuid;
    public $breadcrumbs;
    public function mount($uuid){
        $this->uuid = $uuid;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisition Approvals', 'link' => route('admin.workflows.approvals.purchaserequisitionlist')],
            ['label' => 'Purchase Requisition Show']
        ];
    }
    public function render()
    {
        return view('livewire.admin.workflows.approvals.purchaserequisitionshow');
    }
}
