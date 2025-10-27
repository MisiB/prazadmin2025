<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement;

use App\Interfaces\repositories\ibudgetInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Budgetdetail extends Component
{
    use Toast;
    public $breadcrumbs = [];
    public $uuid;
    protected $budgetrepo;
    public $selectedTab = "budget-tab";
    public $viewmodal = false;
    public $budgetitem_id;
    public $budgetitem;
    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->breadcrumbs = [
            [
                "label" => "Dashboard",
                "link" => route("admin.home"),
            ],
            [
                "label" => "Budgets",
                "link" => route("admin.finance.budgetmanagement.budgets"),
            ],
            [
                "label" => "Budget Management"
            ],
        ];
    }
    public function boot(ibudgetInterface $budgetInterface)
    {
        $this->budgetrepo = $budgetInterface;
    }
    public function getbudget()
    {
       return $this->budgetrepo->getbudgetbyuuid($this->uuid);
     
    }

    public function approvebudget()
    {
        $response=$this->budgetrepo->approvebudget($this->getbudget()->id);
        if($response['status']=="success"){
            $this->success('Budget approved successfully');
        }else{
            $this->error($response['message']);
        }
    }
    public function view($id)
    {
        $this->budgetitem_id = $id;
        $this->budgetitem = $this->budgetrepo->getbudgetitem($this->budgetitem_id);
        $this->viewmodal = true;
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.budgetdetail',[
            "budget" => $this->getbudget()
        ]);
    }
}
