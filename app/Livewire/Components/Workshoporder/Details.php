<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;
use App\Interfaces\services\iworkshopService;
class Details extends Component
{
    public $workshop;
    public $status;
    protected $workshopService;
    public function boot(iworkshopService $workshopService)
    {
        $this->workshopService = $workshopService;
    }
    public function mount($workshop,$status)
    {
        $this->workshop = $workshop;
        $this->status = $status;
    }

    #[On('orderupdated')]
    public function orderupdated(){
        $this->dispatch('ordercreated');
    }
    public function getorders(){
        return $this->workshop->orders->where('status',$this->status);
    }
    public function getheaders():array{
        return [
            ['key'=>'customer','label'=>'Customer'], 
            ['key'=>'delegates','label'=>'Delegates'],
            ['key'=>'amount','label'=>'Amount'], 
            ['key'=>'status','label'=>'Status'],
            ['key'=>'actions','label'=>'']
        ];
    }

    public function deleteorder($id){
        $result = $this->workshopService->deleteorder($id);
        if($result['status'] == 'success'){
            $this->dispatch('orderdeleted');
        }else{
            $this->error('message',$result['message']);
        }
    }
    public function render()
    {
        return view('livewire.components.workshoporder.details',[
            'orders' => $this->getorders(),
            'headers' => $this->getheaders()
        ]);
    }
}
