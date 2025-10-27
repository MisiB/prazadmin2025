<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;
use App\Interfaces\repositories\ibanktransactionInterface;

class Banktransactions extends Component
{
    public $selectedTab = "latest-tab";
    public $breadcrumbs;
    protected $repo;
    public function boot(ibanktransactionInterface $repo)
    {
        $this->repo = $repo;
   
    }
    public function mount(  )
    {
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Bank Transactions']
        ];
    }
    public function getlatesttransactions()
    {
        $response = $this->repo->getlatesttransactions();
        return $response;
    }
    public function render()
    {
        return view('livewire.admin.finance.banktransactions',[
            'latesttransactions' => $this->getlatesttransactions()
        ]);
    }
}
