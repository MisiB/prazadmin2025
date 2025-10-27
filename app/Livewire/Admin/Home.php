<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\iworkplanInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class Home extends Component
{
    use Toast;
    protected $repository;
    protected $workplanrepository;
    public $year;
    public $search;
    public $title;
    public $priority;
    public $individualoutputbreakdown_id;
    public $description;
    public $status;
    public $start_date;
    public $end_date;
    public $contribution;
    public $id;
    public $statusfilter;
    public $priorityfilter;
    public $selectedtask=null;
    public $analysisstatus;
    public $analysiscomment;

    public $totalpending=0;
    public $totalongoing=0;
    public $totalcompleted=0;
    public $totaloverdue=0;
    public $totaldue=0; 
    public bool $addtaskmodal=false;
    public bool $viewtaskmodal=false;
    public bool $link = false;
    public array $myChart = [
        'type' => 'pie',
        'data' => [
            'labels' => ['Linked', 'Not Linked'],
            'datasets' => [
                [
                    'label' => '# of Tasks',
                    'data' => [12, 19],
                ]
            ]
        ]
    ];
    
    
    public function boot(itaskInterface $repository,iworkplanInterface $workplanrepository)
    {
        $this->repository = $repository;
        $this->workplanrepository = $workplanrepository;
    }

    public function mount(){
        $this->year = Carbon::now()->year;
        $this->start_date = Carbon::now()->startOfWeek()->nextWeekday()->format('Y-m-d');
        $this->end_date = Carbon::now()->endOfWeek()->previousWeekday()->format('Y-m-d');
    }

    public function getmytasks(){
      $tasks=  $this->repository->getmytasks($this->year);
      if($this->statusfilter != ""){
        $tasks = $tasks->where("status",$this->statusfilter);
      }
      if($this->priorityfilter != ""){
        $tasks = $tasks->where("priority",$this->priorityfilter);
      }
      return $tasks;
    }
    public function getmyworkplanbreakdownlist(){
        return $this->workplanrepository->getworkplabreakdownbyuser(Auth::user()->id,$this->year);
    }

    public function statuslist():array{
        return [
            ["id"=>"pending","name"=>"Pending"],
            ["id"=>"ongoing","name"=>"Ongoing"],
            ["id"=>"completed","name"=>"Completed"]
        ];
    }

    public function prioritylist():array{
        return [
            ["id"=>"High","name"=>"High"],
            ["id"=>"Medium","name"=>"Medium"],
            ["id"=>"Low","name"=>"Low"]
        ];
    }

    public function edit($id){
       $task = $this->repository->gettask($id); 
       $this->id = $task->id;
       $this->title = $task->title;
       $this->priority = $task->priority;
       $this->individualoutputbreakdown_id = $task->individualoutputbreakdown_id;
       $this->description = $task->description;
       $this->status = $task->status;
       $this->start_date = $task->start_date;
       $this->end_date = $task->end_date;
       $this->addtaskmodal=true;
    }

    public function save(){
        $this->validate([
            "title"=>"required",
            "priority"=>"required",
            "description"=>"required",
            "start_date"=>"required",
            'individualoutputbreakdown_id'=>'required_if:link,true',
            'contribution'=>'required_if:link,true',
            "end_date"=>"required",
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }

        $this->reset([
            "title",
            "priority",
            "description",
            "start_date",
            "end_date",
            "id"
        ]);

    }

    public function create(){
       $responses = $this->repository->createtask([
            "title"=>$this->title,
            "priority"=>$this->priority,
            "description"=>$this->description,
            "start_date"=>$this->start_date,
            "end_date"=>$this->end_date,
            "user_id"=>Auth::user()->id,
            "contribution"=>$this->contribution==null?0:$this->contribution,
            "individualoutputbreakdown_id"=>$this->individualoutputbreakdown_id
        ]);
        if($responses["status"] == "success"){
            $this->success($responses["message"]);
        }else{
            $this->error($responses["message"]);
        }
    }

    public function update(){
       $response= $this->repository->updatetask($this->id,[
            "title"=>$this->title,
            "priority"=>$this->priority,
            "description"=>$this->description,
            "start_date"=>$this->start_date,
            "end_date"=>$this->end_date,
            "user_id"=>Auth::user()->id,
            "contribution"=>$this->contribution==null?0:$this->contribution,
            "individualoutputbreakdown_id"=>$this->individualoutputbreakdown_id
        ]);
        if($response["status"] == "success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function deletetask($id){
       $responses = $this->repository->deletetask($id);  
       if($responses["status"] == "success"){
           $this->success($responses["message"]);
       }else{
           $this->error($responses["message"]);
       }
    }
    public function viewtask($id){
        $this->selectedtask = null;
         $task =$this->repository->gettask($id);
         $this->selectedtask = $task;
        $this->viewtaskmodal=true;
    }
    public function marktask($id){
        $response = $this->repository->marktask($id,"completed");
        if($response["status"] == "success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function marktaskasongoing($id){
        $response = $this->repository->marktask($id,"ongoing");
        if($response["status"] == "success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function marktaskascompleted($id){
        $response = $this->repository->marktask($id,"completed");
        if($response["status"] == "success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function analysis(){
       $tasks = $this->getmytasks();
       $totallinkedtostrategy = 0;
       $totalnotlinkedtostrategy = 0;
       if(count($tasks)>0){
       foreach($tasks as $task){
           if($task->individualoutputbreakdown_id != null){
               $totallinkedtostrategy++;
           }else{
               $totalnotlinkedtostrategy++;
           }
       }
       $percentlinkedtostrategy = round(($totallinkedtostrategy / count($tasks)) * 100);
       $percentnotlinkedtostrategy = round(($totalnotlinkedtostrategy / count($tasks)) * 100);  
       
       
       $this->analysiscomment = $percentlinkedtostrategy > $percentnotlinkedtostrategy ? $percentlinkedtostrategy . "% of your tasks are linked to workplan  which is greater than " . $percentnotlinkedtostrategy . "% of your tasks are not linked" : $percentlinkedtostrategy . "% of your tasks are linked to your workplan which is less than " . $percentnotlinkedtostrategy . "% of your tasks are not linked";
       $this->analysisstatus = $percentlinkedtostrategy > $percentnotlinkedtostrategy? "Good" : "Bad";
    }

    }

    public function computesummaries(){
        $tasks = $this->getmytasks();
        $this->totalpending = $tasks->where("status","pending")->count();
        $this->totalongoing = $tasks->where("status","ongoing")->count();
        $this->totalcompleted = $tasks->where("status","completed")->count();
        $this->totaloverdue = $tasks->whereIn("status",["pending","ongoing"])->where("end_date","<",Carbon::now())->count();
        $this->totaldue = $tasks->whereIn("status",["pending","ongoing"])->where("end_date","=",Carbon::now())->count();
    }

   

    public function render()
    {
        return view('livewire.admin.home',[
            'tasks'=>$this->getmytasks(),
            'breakdownlist'=>$this->getmyworkplanbreakdownlist(),
            'statuslist'=>$this->statuslist(),
            'prioritylist'=>$this->prioritylist(),
            "analysis"=>$this->analysis(),
            "compute"=>$this->computesummaries()
        ]);
    }
}
