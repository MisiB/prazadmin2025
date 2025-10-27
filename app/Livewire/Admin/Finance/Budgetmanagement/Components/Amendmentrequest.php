<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use App\Interfaces\repositories\ibudgetInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Amendmentrequest extends Component
{
    use Toast;
    public $budget;
    protected $budgetrepo;
    public function boot(ibudgetInterface $budgetrepo)
    {
        $this->budgetrepo = $budgetrepo;
    }
    public function mount($budget)
    {
        $this->budget = $budget;
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.amendmentrequest',[
            'budget' => $this->budget
        ]);
    }
    public function approvebudgetitem($budgetitem_id)
    {
        $response =  $this->budgetrepo->approvebudgetitem($budgetitem_id);
        if($response["status"] == "success"){
            $this->success('Budget Item Approved Successfully');
            $this->dispatch('budgetitemapproved', $budgetitem_id);
        }else{
            $this->error($response["message"]);
        }
    }
    public function approveall()
    {
        $response =  $this->budgetrepo->approvebudget($this->budget->id);
        if($response["status"] == "success"){
            $this->success('Budget Item Approved Successfully');
            $this->dispatch('budgetitemapproved', $this->budget->id);
        }else{
            $this->error($response["message"]);
        }
    }
}
