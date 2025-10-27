<?php

namespace App\Livewire\Admin\Customers;

use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;
    public $breadcrumbs = [];
    public $customer;
    public $walletBalances = [];
    protected $customerrepo;
    protected $suspenserepo;
    
    public function boot(icustomerInterface $customerrepo,isuspenseInterface $suspenserepo)
    {
        $this->customerrepo = $customerrepo;
        $this->suspenserepo = $suspenserepo;
    }
    
    public function mount($id){
        $this->customer = $this->customerrepo->getCustomerById($id);
        $this->calculateWalletBalances();
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Customers','link'=>route('admin.customers.showlist')],
            ['label' => $this->customer?->name]
        ];
    }
    
    protected function calculateWalletBalances()
    {
       $this->walletBalances = $this->suspenserepo->getsuspensewallet($this->customer->regnumber);
    }
    
    public function render()
    {
        return view('livewire.admin.customers.show');
    }
}
