<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use App\Interfaces\repositories\ibudgetInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Viramentrequest extends Component
{
    use Toast;
    public $budget;
    public $comment;
    public $modal = false;
    public $id;
    protected $budgetrepo;
    public function mount($budget){
        $this->budget = $budget;
    }

    public function boot(ibudgetInterface $budgetrepo){
        $this->budgetrepo = $budgetrepo;
    }

   public function approve($id){
    $response = $this->budgetrepo->approvebudgetvirement($id);
    if($response['status']=="success"){
        $this->success('Budget Virement Approved Successfully');
    }else{
        $this->error('Budget Virement Approval Failed');
    }
   }
   public function openrejectionmodal($id){
    $this->modal = true;
    $this->id = $id;
   }
   public function reject(){
    $this->validate([
        'comment'=>'required'
    ]);
    $response = $this->budgetrepo->rejectbudgetvirement(["id"=>$this->id,"comment"=>$this->comment]);
    if($response['status']=="success"){
        $this->success('Budget Virement Rejected Successfully');
    }else{
        $this->error('Budget Virement Rejection Failed');
    }
    $this->modal = false;
    
   }
   public function closeModal(){
    $this->modal = false;
   }
   
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.viramentrequest',[
            "budget"=>$this->budget
        ]);
    }
}
