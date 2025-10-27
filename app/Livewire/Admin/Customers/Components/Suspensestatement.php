<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;

class Suspensestatement extends Component
{
    public $customer_id;
    protected $suspenserepo;
    public $breadcrumbs=[];
    public $showsuspense=null;
    public $showmodal=false;
    public function boot(isuspenseInterface $suspenserepo)
    {
        $this->suspenserepo = $suspenserepo;
    }
    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->showsuspense = null;
        $this->breadcrumbs=[
            ["link" => route("admin.customers.showlist"),"label"=>"Customers"],
            ["link" => route("admin.customers.show", $this->customer_id),"label"=>"Customer"],
            ["label"=>"Suspense Statement"],
        ];
    }

    public function getsuspenselist(){
        return $this->suspenserepo->getsuspensestatement($this->customer_id);
    }

    public function headers():array{
        return [
            ["key"=>"sourcetype","label"=>"Source Type"],
            ["key"=>"type","label"=>"Account Type"],
            ["key"=>"accountnumber","label"=>"Account Number"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"utilized","label"=>"Utilized"],
            ["key"=>"balance","label"=>"Balance"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"action","label"=>"Action"],
        ];
    }
    public function showSuspense($id){
        $this->showsuspense = $this->suspenserepo->getsuspense($id);
        $this->showmodal=true;
    }
    public function render()
    {
        return view('livewire.admin.customers.components.suspensestatement',[
            "suspenselist"=>$this->getsuspenselist(),
            "headers"=>$this->headers(),
        ]);
    }
}
