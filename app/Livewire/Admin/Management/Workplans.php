<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\istrategyInterface;
use Carbon\Carbon;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Collection;
use App\Interfaces\repositories\individualworkplanInterface;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\repositories\idepartmentInterface;
class Workplans extends Component
{
    use Toast;
    protected $strategyrepo;
    protected $individualworkplanrepo;

    protected $departmentrepo;
    public $year;
    public $strategy_id;
    public $programme_id;
    public $outcome_id;
    public $output_id;
    public $approver_id;
    public $indicator_id;
    public $target_id;
    public $targetmatrix_id;
    public $departmentoutput_id;
    public $departmentoutput;
    public $month;
    public $target;
    public $weightage;
    public $status;
    public $id;
    public $output;
    public $indicator;

    public $workplans;
    public  $strategy;
    public  $addworkplanmodal=false;
    public array $breadcrumbs =[];
    public $modal=false;
    public $programmes;
    public $outcomes;
    public $outputs;
    public $indicators;
    public $monthlist;
    public $targetmatrices;
    public $myapprover;
    public $targets;
    public function boot(istrategyInterface $strategyrepo,individualworkplanInterface $individualworkplanrepo,idepartmentInterface $departmentrepo)
    {
      $this->strategyrepo = $strategyrepo;
      $this->individualworkplanrepo = $individualworkplanrepo;
      $this->departmentrepo = $departmentrepo;
    }

    public function mount(){
        $this->year = Carbon::now()->year;
        $this->programmes = new Collection();
        $this->outcomes = new Collection();
        $this->outputs = new Collection();
        $this->indicators = new Collection();
        $this->targetmatrices = new Collection();
        $this->departmentoutputs = new Collection();
        $this->workplans = new Collection();
        $this->targets = new Collection();
        $this->strategy = null;
        $this->monthlist = new Collection();
        $this->breadcrumbs = [
            [
                'label' => 'Home',
                'link' => route('admin.home'),
            ],
            [
                'label' => 'Workplans',
            ],
        ];
       
    }
    public function getstrategies(){
        $data = $this->strategyrepo->getstrategies();
      
        return $data;
    }
    public function getsupervisor(){
        $supervisor = $this->departmentrepo->getsupervisor(Auth::user()->id);
        $this->myapprover = $supervisor->supervisor->name." ".$supervisor->supervisor->surname;
        $this->approver_id = $supervisor->reportto;
        return $supervisor;
    }
    public function getmonthlist(){
        if($this->targetmatrix_id){
            $targetmatrix = $records = $this->targets->where("id",$this->target_id)->first()->targetmatrices->where("id",$this->targetmatrix_id)->first();
            if($targetmatrix){
                 if($targetmatrix->month == 'Q1'){
                    $this->monthlist = collect([['id'=>'JAN','name'=>'January'],['id'=>'FEB','name'=>'February'],['id'=>'MAR','name'=>'March']]);
                 }elseif($targetmatrix->month == 'Q2'){
                    $this->monthlist = collect([['id'=>'APR','name'=>'April'],['id'=>'MAY','name'=>'May'],['id'=>'JUN','name'=>'June']]);
                 }elseif($targetmatrix->month == 'Q3'){
                    $this->monthlist = collect([['id'=>'JUL','name'=>'July'],['id'=>'AUG','name'=>'August'],['id'=>'SEP','name'=>'September']]);
                 }elseif($targetmatrix->month == 'Q4'){
                    $this->monthlist = collect([['id'=>'OCT','name'=>'October'],['id'=>'NOV','name'=>'November'],['id'=>'DEC','name'=>'December']]);
                 }
            }
        }

      
    }
    public function getworkplans(){
        $this->validate([
            "strategy_id"=>"required",
            "year"=>"required"
        ]);
        $workplans = $this->individualworkplanrepo->getindividualworkplans(Auth::user()->id,$this->strategy_id,$this->year);
  
        $this->workplans = collect($workplans);
        $this->modal = false;
    }
   
    public function addworkplan(){
        $this->strategy = $this->strategyrepo->getstrategybydepartment($this->strategy_id,Auth::user()->department->department_id,$this->year);
     
         $this->addworkplanmodal = true;
    }
    public function populatefields(){
        if($this->strategy !=null){
            $this->programmes = $this->strategy->programmes;
            if($this->programme_id){
                $this->outcomes = $this->programmes->where("id",$this->programme_id)->first()->outcomes;
            }
            if($this->outcome_id){
                $this->outputs = $this->outcomes->where("id",$this->outcome_id)->first()->outputs;
            }
            if($this->output_id){
                $this->indicators = $this->outputs->where("id",$this->output_id)->first()->departmentoutputs->first()->indicators;
            }
            if($this->indicator_id){
                $this->targets = $this->indicators->where("id",$this->indicator_id)->first()->targets;
            }
            if($this->target_id){
                $records = $this->targets->where("id",$this->target_id)->first()->targetmatrices;
                if($records->count() > 0){
                    $data = [];
                    foreach($records as $record){
                        $data[]= ['id'=>$record->id,'name'=>'Quarter: '.$record->month.' Target: '.$record->target];
                    }
                    $this->targetmatrices = collect($data);
                }
            }
            if($this->targetmatrix_id){
                $this->getmonthlist();
            }
         }
    }
    public function saveworkplan(){
        $this->validate([
            "strategy_id"=>"required",
            "year"=>"required",  
            "targetmatrix_id"=>"required",
            "month"=>"required",
            'output'=>'required',
            "target"=>"required",
            "weightage"=>"required",
        ]);
        $response = $this->individualworkplanrepo->createindividualworkplan([
            "strategy_id"=>$this->strategy_id,
            "year"=>$this->year,
            "approver_id"=>$this->approver_id,
            "user_id"=>Auth::user()->id,
            "targetmatrix_id"=>$this->targetmatrix_id,
            "month"=>$this->month,
            "output"=>$this->output,
            "indicator"=>$this->indicator,
            "target"=>$this->target,
            "weightage"=>$this->weightage,
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->addworkplanmodal = false;
            $this->getworkplans();
        }else{
            $this->error($response['message']);
        }
        $this->reset([
            "output","indicator","target","weightage",
            "month","targetmatrix_id"
        ]);
    }
    public function getworkplan($id){
        $workplan = $this->individualworkplanrepo->getindividualworkplan($id);
        if($workplan){
            $this->id = $workplan->id;
            $this->output = $workplan->output;
            $this->indicator = $workplan->indicator;
            $this->targetmatrix_id = $workplan->targetmatrix_id;
            $this->month = $workplan->month;
            $this->target = $workplan->target;
            $this->weightage = $workplan->weightage;
            $this->strategy_id = $workplan->strategy_id;
            $this->year = $workplan->year;
            $this->approver_id = $workplan->approver_id;
            $this->user_id = $workplan->user_id;
            $this->addworkplanmodal = true;
        }
    }
    public function deleteworkplan($id){
        $response = $this->individualworkplanrepo->deleteindividualworkplan($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->getworkplans();
        }else{
            $this->error($response['message']);
        }
    }
/*
    public function addworkplan($subprogrammeoutput_id){
        $this->subprogrammeoutput_id = $subprogrammeoutput_id;  
        $workplan = $this->workplans->where("subprogrammeoutput_id", $subprogrammeoutput_id)->first();
        if($workplan && $workplan["supervisoroutput_id"]){
            $this->parent_id = $workplan["supervisoroutput_id"];      
        }
        $this->addmodal = true;
    }

    public function save(){
        $this->validate([
            "output"=>"required",
            "indicator"=>"required",
            "target"=>"required",
            "variance"=>"required",
            "weightage"=>"required",
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(["output","indicator","target","variance","weightage"]);
    
    }
    public function editoutput($id){
        $workplan = $this->repo->getworkplan($id);
        $this->id = $workplan->id;
        $this->output = $workplan->output;
        $this->indicator = $workplan->indicator;
        $this->target = $workplan->target;
        $this->variance = $workplan->variance;
        $this->weightage = $workplan->weightage;
        $this->parent_id = $workplan->parent_id;
        $this->subprogrammeoutput_id = $workplan->subprogrammeoutput_id;
        $this->addmodal = true;
    }

    public function create(){
       $response = $this->repo->createworkplan([
            "output"=>$this->output,
            "indicator"=>$this->indicator,
            "target"=>$this->target,
            "variance"=>$this->variance,
            "weightage"=>$this->weightage,
            "parent_id"=>$this->parent_id,
            "subprogrammeoutput_id"=>$this->subprogrammeoutput_id
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->addmodal = false;
            $this->getworkplans();
        }else{
            $this->error($response['message']);
        }

    }
    public function update(){
        $response = $this->repo->updateworkplan($this->id, [
            "output"=>$this->output,
            "indicator"=>$this->indicator,
            "target"=>$this->target,
            "variance"=>$this->variance,
            "parent_id"=>$this->parent_id,
            "weightage"=>$this->weightage,
            "subprogrammeoutput_id"=>$this->subprogrammeoutput_id
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->addmodal = false;
            $this->getworkplans();
        }else{
            $this->error($response['message']);
        }
    }
    public function deleteoutput($id){
        $response = $this->repo->deleteworkplan($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->getworkplans();
        }else{
            $this->error($response['message']);
        }
    }
    public function getsubordinates($id){
        $this->individualoutput_id = $id;
        $this->subordinates = $this->departmentrepo->getmysubordinates();
        $this->getassignees();
        $this->assignemodal = true;

    }
    public function getassignees(){
        $this->assigneelist = $this->repo->getworkplanassignees($this->individualoutput_id);
    }
    public function selectassign($id){
        $this->newassignemodal = true;
        $this->user_id = $id;
    }
    public function saveassignee(){
        $this->validate([
            "target"=>"required",
        ]);
       
        if($this->assignee_id){
            $this->updateassignee();
        }else{
            $this->createassignee();
        }
        $this->reset([
            "assignee_id",
            "user_id",
            "target",
            "variance"
        ]);
        $this->getassignees();
    }
    public function createassignee(){
        $this->user_id = $this->subordinates->where("id", $this->user_id)->first()->user_id;
        $response = $this->repo->createworkplanassignee([
            "individualoutput_id"=>$this->individualoutput_id,
            "user_id"=>$this->user_id,
            "target"=>$this->target,
            "variance"=>$this->variance
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->newassignemodal = false;
        }else{
            $this->error($response['message']);
        }
    }
    public function editassign($id){
        $assignee = $this->repo->getworkplanassignee($id);
        $this->assignee_id = $assignee->id;
        $this->user_id = $assignee->user_id;
        $this->target = $assignee->target;
        $this->newassignemodal = true;
    }
    public function updateassignee(){
        $response = $this->repo->updateworkplanassignee($this->assignee_id, [
            "target"=>$this->target,
            "variance"=>$this->variance
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->newassignemodal = false;
        }else{
            $this->error($response['message']);
        }
    }
    public function deleteassignee($id){
        $response = $this->repo->deleteworkplanassignee($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->getassignees();
        }else{
            $this->error($response['message']);
        }
    }

    public function getbreakdown($id){
        $this->individualoutput_id = $id;
        $this->breakdownlist = $this->repo->getworkplanbreakdownlist($id);
        $this->breakdownmodal = true;
    }

    public function savebreakdown(){
        $this->validate([
            "month"=>"required",
            "contribution"=>"required",
            "description"=>"required",
            "output"=>"required",
        ]);
        if($this->breakdown_id){
            $this->updatebreakdown();
        }else{
            $this->createbreakdown();
        }
        $this->reset([
            "month",
            "contribution",
            "description",
            "output"
        ]);        
    }
    public function createbreakdown(){
        $response=$this->repo->createworkplanbreakdown([
            "individualoutput_id"=>$this->individualoutput_id,
            "month"=>$this->month,
            "contribution"=>$this->contribution,
            "description"=>$this->description,
            "output"=>$this->output
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->breakdownlist = $this->repo->getworkplanbreakdownlist($this->individualoutput_id);
        }else{
            $this->error($response['message']);
        }
    }
    public function editbreakdown($id){
        $breakdown = $this->repo->getworkplanbreakdown($id);
        $this->breakdown_id = $breakdown->id;
        $this->month = $breakdown->month;
        $this->contribution = $breakdown->contribution;
        $this->description = $breakdown->description;
        $this->output = $breakdown->output;
        $this->addbreakdownmodal = true;
    }
    public function updatebreakdown(){
        $response = $this->repo->updateworkplanbreakdown($this->breakdown_id, [
            "month"=>$this->month,
            "contribution"=>$this->contribution,
            "description"=>$this->description,
            "output"=>$this->output
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->addbreakdownmodal = false;
            $this->breakdownlist = $this->repo->getworkplanbreakdownlist($this->individualoutput_id);
        }else{
            $this->error($response['message']);
        }
    }
    public function deletebreakdown($id){
        $response = $this->repo->deleteworkplanbreakdown($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->breakdownlist = $this->repo->getworkplanbreakdownlist($this->individualoutput_id);
        }else{
            $this->error($response['message']);
        }
    }*/
    public function render()
    {
        $this->populatefields();
        return view('livewire.admin.management.workplans',[
            "strategies"=>$this->getstrategies(),
            "monthlist"=>$this->getmonthlist(),
            "supervisor"=>$this->getsupervisor()
            ]);
    }
}
