<?php

namespace App\Livewire\Admin\Workflows;

use Livewire\Component;

class Purchaserequisition extends Component
{
    public $breadcrumbs =[];
    public $uuid;
    public function mount($uuid){
        $this->uuid = $uuid;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisitions', 'link' => route('admin.workflows.purchaserequisitions')],
            ['label' => 'Purchase Requisition']
        ];
    }
  
   
  
  
    public function render()
    {
        return view('livewire.admin.workflows.purchaserequisition',[
            "breadcrumbs"=>$this->breadcrumbs,
        ]);
    }
}
