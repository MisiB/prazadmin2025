<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement;

use App\Interfaces\repositories\ibudgetconfigurationInterface;
use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\repositories\isubprogrammeoutInterface;
use App\Interfaces\repositories\iworkplanInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Exception;

class Departmentalbudget extends Component
{
    use Toast;
    public $year;
    public $budget_id;
    public array $breadcrumbs=[];
    protected $budgetrepo;
    protected $budgetconfigurationrepo;
    protected $subprogrammeoutputrepo;
    protected $workplanrepo;
    protected $strategyrepo;

    public $budget;

    public $activity;
    public $description;
    public $expensecategory_id;
    public $sourceoffund_id;
    public $strategysubprogrammeoutput_id;
    public $quantity;
    public $unitprice;
    public $total;
    public $status;
    public $currency_id;
    public $strategy_id;
    public $focusdate;
    public $id;
    public $modal=false;
    public $workplans;
    public $selectedTab="budget-tab";
    public $totalbudget =0;
    public $totalutilized =0;
    public $totalremaining =0;
    public $viewmodal = false;
    public $budgetitem_id;
    public $budgetitem;
    public $totalincomingvirements;
    public $totaloutgoingvirements;
    public $myTab = "purchase-tab";

    public function boot(ibudgetInterface $budgetrepo,ibudgetconfigurationInterface $budgetconfigurationrepo,iworkplanInterface $workplanrepo,istrategyInterface $strategyrepo,isubprogrammeoutInterface $subprogrammeoutputrepo)
    {
        $this->budgetrepo = $budgetrepo;
        $this->budgetconfigurationrepo = $budgetconfigurationrepo;
        $this->workplanrepo = $workplanrepo;
        $this->strategyrepo = $strategyrepo;
        $this->subprogrammeoutputrepo = $subprogrammeoutputrepo;
    }
    public function mount()
    {
        $this->year = date("Y");
        $this->workplans = new Collection();
        $this->budget = null;
        $this->breadcrumbs = [
            ["label" => "Home", "link" => route("admin.home")],
            ["label" => "Departmental Budget"],
        ];
    }

    public function getbudgets()
    {
        $budgets = $this->budgetrepo->getbudgets();
        $this->budget_id = $budgets->last()->id;
        $this->currency_id = $budgets->last()->currency_id;
        $this->year = $budgets->last()->year;
        $this->budget = $budgets->last();
        return $budgets;
    }
    public function getstrategies()
    {
     $strategies = $this->strategyrepo->getstrategies();
     $this->strategy_id = $strategies->last()->id;
     return $strategies;
    }

    public function getsourceoffunds()
    {
        return $this->budgetconfigurationrepo->getsourceoffunds();
    }

    public function getexpensecategories()
    {
        return $this->budgetconfigurationrepo->getexpensecategories();
    }
    public function getoutputs(){
       
       return $this->subprogrammeoutputrepo->getsubprogrammeoutputbydepartment($this->strategy_id,$this->year,Auth::user()->department->department_id);
    
      
    
    }

   

    public function getbudgetitems()
    {   
        return $this->budgetrepo->getbudgetitemsbydepartment($this->budget_id,Auth::user()->department->department_id);
    }
    public function computetotals(){
        $budgetitems = $this->getbudgetitems();
        $this->totalbudget = $budgetitems->where('status','APPROVED')->sum("total");
        $this->totalincomingvirements = 0;
        $this->totaloutgoingvirements = 0;
        $budgetitems->map(function($budgetitem){
            $this->totalincomingvirements += $budgetitem->incomingvirements->where('status','APPROVED')->sum("amount");
            $this->totaloutgoingvirements += $budgetitem->outgoingvirements->where('status','APPROVED')->sum("amount");
        });

        $this->totalbudget= $this->totalbudget-$this->totalincomingvirements;
        $this->totalutilized = $this->totalutilized+$this->totaloutgoingvirements;
        $this->totalremaining = $this->totalbudget-$this->totalutilized;
    }

    public function edit($id){
        $budgetitem = $this->budgetrepo->getbudgetitem($id);
        $this->id = $budgetitem->id;
        $this->activity = $budgetitem->activity;
        $this->description = $budgetitem->description;
        $this->expensecategory_id = $budgetitem->expensecategory_id;
        $this->sourceoffund_id = $budgetitem->sourceoffund_id;
        $this->strategysubprogrammeoutput_id = $budgetitem->strategysubprogrammeoutput_id;
        $this->quantity = $budgetitem->quantity;
        $this->unitprice = $budgetitem->unitprice;
        $this->total = $budgetitem->total;
        $this->status = $budgetitem->status;
        $this->focusdate = $budgetitem->focusdate;
        $this->modal = true;
    }

    public function updatedQuantity($value){
        $this->total = $value * $this->unitprice;
    }

    public function updatedUnitprice($value){
        $this->total = $this->quantity * $value;
    }

   

    public function headers(){

        return [
            ['key'=>'activity','label'=>'Activity'],
            ['key'=>'expensecategory.name','label'=>'Expense Category'],
            ['key'=>'sourceoffund.name','label'=>'Source of Funds'],
            ['key'=>'quantity','label'=>'Quantity'],
            ['key'=>'unitprice','label'=>'Unit Price'],
            ['key'=>'total','label'=>'Total'],
            ['key'=>'utilized','label'=>'Utilized'],
            ['key'=>'remaining','label'=>'Remaining'],
            ['key'=>'status','label'=>'Status'],
            ['key'=>'action','label'=>'']
        ];
    }

    public function save(){
        try {
            
       
        
        
            $this->validate([
                'activity'=>'required',
                'description'=>'required',
                'expensecategory_id'=>'required',
                'sourceoffund_id'=>'required',
                'strategysubprogrammeoutput_id'=>'required',
                'quantity'=>'required',
                'unitprice'=>'required',
                'total'=>'required',
                'focusdate'=>'required',
            ]);
            
            // Set default status for new items
            if (!$this->status) {
                $this->status = 'pending';
            }

            if($this->id){
                $this->update();
            }else{
                $this->create();
            }

            $this->modal = false; // Close the modal after successful save
            $this->reset(['activity','description','expensecategory_id','sourceoffund_id',
                         'strategysubprogrammeoutput_id','quantity','unitprice','total','status',
                         'focusdate','id']);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }

    public function create(){
       $response = $this->budgetrepo->createbudgetitem([
        'budget_id'=>$this->budget_id,
        'activity'=>$this->activity,
        'description'=>$this->description,
        'expensecategory_id'=>$this->expensecategory_id,
        'sourceoffund_id'=>$this->sourceoffund_id,
        'strategysubprogrammeoutput_id'=>$this->strategysubprogrammeoutput_id,
        'quantity'=>$this->quantity,
        'unitprice'=>$this->unitprice,
        'total'=>$this->total,
        'status'=>$this->status,
        'focusdate'=>$this->focusdate,
        'currency_id'=>$this->currency_id,
        'department_id'=>Auth::user()->department->department_id,
        'created_by'=>Auth::user()->name,
        'status'=>'PENDING'
       ]); 
       if($response['status']=="success"){
        $this->success('Budget Item Created Successfully');
       }else{
        $this->error('Budget Item Creation Failed');
       } 
    }
    public function view($id)
    {
        $this->budgetitem_id = $id;
        $this->budgetitem = $this->budgetrepo->getbudgetitem($this->budgetitem_id);
        $this->viewmodal = true;
    }

    public function update(){
       $response = $this->budgetrepo->updatebudgetitem($this->id,[
        'activity'=>$this->activity,
        'description'=>$this->description,
        'expensecategory_id'=>$this->expensecategory_id,
        'sourceoffund_id'=>$this->sourceoffund_id,
        'strategysubprogrammeoutput_id'=>$this->strategysubprogrammeoutput_id,
        'quantity'=>$this->quantity,
        'unitprice'=>$this->unitprice,
        'total'=>$this->total,
        'status'=>$this->status,
        'focusdate'=>$this->focusdate,
        'updated_by'=>Auth::user()->name,
        'status'=>'PENDING'
       ]); 
       if($response['status']=="success"){
        $this->success('Budget Item Updated Successfully');
       }else{
        $this->error('Budget Item Update Failed');
       } 
    }

    public function delete($id){
       $response = $this->budgetrepo->deletebudgetitem($id); 
       if($response['status']=="success"){
        $this->success('Budget Item Deleted Successfully');
       }else{
        $this->error('Budget Item Deletion Failed');
       } 
    }
    
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.departmentalbudget',
        [
            'budgets'=>$this->getbudgets(),
            'budgetitems'=>$this->getbudgetitems(),
            'headers'=>$this->headers(),
            'sourceoffunds'=>$this->getsourceoffunds(),
            'expensecategories'=>$this->getexpensecategories(),
            'strategies'=>$this->getstrategies(),
            'outputs'=>$this->getoutputs(),
            'summary'=>$this->computetotals(),
        ]);
    }
}
