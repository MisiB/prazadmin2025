<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement;


use App\Interfaces\repositories\ibudgetInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Departmentalbudgetdetail extends Component
{
    use Toast;
    public $uuid;
    public $id;
    protected $budgetrepo;
    public $breadcrumbs=[];
    public $modal=false;
    public $myTab = "purchase-tab";
    public $budget_id;
    public $form_budgetitem;
    public $to_budgetitem;
    public $amount;
    public $description;
    public $budgetitem;
    public $totalincomingvirements;
    public $totaloutgoingvirements;
    public function boot(ibudgetInterface $budgetrepo)
    {
        $this->budgetrepo = $budgetrepo;
    }
    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->getbreadcrumb();
    }
    public function getbreadcrumb()
    {
        return $this->breadcrumbs = [
            ["label" => "Home", "link" => route("admin.home")],
            ["label" => "Budget Management", "link" => route("admin.finance.budgetmanagement.budgets")],
            ["label" => "Departmental Budget", "link" => route("admin.finance.budgetmanagement.departmentalbudgets")],
            ["label" => "Departmental Budget Detail"],
        ];
    }
    public function getbudgetitem()
    {
        $payload= $this->budgetrepo->getbudgetitembyuuid($this->uuid);
        $this->budget_id = $payload->budget_id;
        $this->form_budgetitem = $payload->id;
        $this->budgetitem = $payload;
        return $payload;
    }
    public function getbudgetitems()
    {   
        return $this->budgetrepo->getbudgetitemsbydepartment($this->budget_id,Auth::user()->department->department_id)->except($this->form_budgetitem);
    }
    public function savevirement()
    {
      $this->validate([
        'form_budgetitem'=>'required',
        'to_budgetitem'=>'required',
        'amount'=>'required|numeric|min:0|max:'.$this->budgetitem->total,
        'description'=>'required',
      ]);
      if($this->id){
        $this->updatevirement();
      }else{
        $this->createvirement();
      }
      $this->reset(['form_budgetitem','to_budgetitem','amount','description','id']);
      $this->modal = false;
    }
    public function createvirement()
    {
        $response = $this->budgetrepo->createbudgetvirement([
            'budget_id'=>$this->budget_id,
            'from_budgetitem_id'=>$this->form_budgetitem,
            'to_budgetitem_id'=>$this->to_budgetitem,
            'amount'=>$this->amount,
            'description'=>$this->description,
            'user_id'=>Auth::user()->id,
            'department_id'=>$this->budgetitem->department_id,
            'status'=>'PENDING'
        ]);
        if($response['status']=="success"){
            $this->success('Budget Virement Created Successfully');
        }else{
            $this->error('Budget Virement Creation Failed');
        }
    }
    public function updatevirement()
    {
        $response = $this->budgetrepo->updatebudgetvirement($this->id, [
            'budget_id'=>$this->budget_id,
            'form_budgetitem_id'=>$this->form_budgetitem,
            'to_budgetitem_id'=>$this->to_budgetitem,
            'amount'=>$this->amount,
            'description'=>$this->description,
            'user_id'=>Auth::user()->id,
            'department_id'=>$this->budgetitem->department_id,
            'status'=>'PENDING'
        ]);
        if($response['status']=="success"){
            $this->success('Budget Virement Updated Successfully');
        }else{
            $this->error('Budget Virement Update Failed');
        }
    }
    public function edit($id)
    {
        $this->id = $id;
        $virement = $this->budgetrepo->getbudgetvirement($this->id);
        $this->form_budgetitem = $virement->form_budgetitem_id;
        $this->to_budgetitem = $virement->to_budgetitem_id;
        $this->amount = $virement->amount;
        $this->description = $virement->description;
        $this->modal = true;
    }
    public function deletevirement($id)
    {
        $response = $this->budgetrepo->deletebudgetvirement($id);
        if($response['status']=="success"){
            $this->success('Budget Virement Deleted Successfully');
        }else{
            $this->error('Budget Virement Deletion Failed');
        }
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.departmentalbudgetdetail',[
            "budgetitem"=>$this->getbudgetitem(),
            "budgetitems"=>$this->getbudgetitems()
        ]);
    }
}
