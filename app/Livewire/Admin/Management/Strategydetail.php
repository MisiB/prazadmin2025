<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\repositories\iprogrammeInterface;
use App\Interfaces\repositories\ioutcomeInterface;
use App\Interfaces\repositories\ioutputInterface;
use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iIndicatorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;

class Strategydetail extends Component
{
    use Toast;
    public $uuid; 
    public $id;
    public $title;
    public $code;
    public $uom;
    public $programme_id;
    public $year;
    public $output_id;
    public $target_id;
    public $target;
    public $variance;
    public $outputmodal = false;
    public bool $modal = false;
    public bool $viewModal = false;
    public $strategy = null;
    public $breadcrumbs = [];
    public array $myChart = [];
    public $programme = null;
    public $outcome_id;
    public $outcomemodal = false;
    public $indicatormodal = false;
    public $targetmodal = false;
   
    public $indicator_id;
    public $departmentoutput_id;
    public $adddepartmentoutputmodal = false;
    public $department_id;
    public float $weightage=0;
    public $outcome = null;
    public $expanded = [];
    public $outcomeexpanded = [];
    public $indicatorexpanded = [];
    public $subprogrammeexpanded = [];
    public $targetmatrixexpanded = [];
    public $outputexpanded = [];
    protected $strategyRepository;
    protected $programmerepository;
    protected $outcomerepository;
    protected $outputrepository;
    protected $departmentrepository;
    protected $indicatorrepository;
    public function boot(istrategyInterface $strategyRepository,iIndicatorInterface $indicatorrepository,iprogrammeInterface $programmerepository,ioutcomeInterface $outcomerepository,ioutputInterface $outputrepository,idepartmentInterface $departmentrepository)
    {
        $this->strategyRepository = $strategyRepository;
        $this->indicatorrepository = $indicatorrepository;
        $this->programmerepository = $programmerepository;
        $this->outcomerepository = $outcomerepository;
        $this->outputrepository = $outputrepository;
        $this->departmentrepository = $departmentrepository;
    }
  public function mount($uuid)
  {
    $this->uuid = $uuid;
    $this->year = date("Y");
    $this->getstrategybyuuid();
    $this->breadcrumbs = [
        [
            'label' => 'Strategies',
            'link' => route('admin.management.strategies'),
        ],
        [
            'label' => $this->strategy->name ?? 'Strategy',
        ],
    ];
 
  }
  public function getstrategybyuuid()
  {
    $payload = $this->strategyRepository->getstrategybyuuid($this->uuid,$this->year);
    if($payload)
    {
      $this->strategy = $payload;
    }
    else{
        return redirect()->route('admin.management.strategies')->with('error','Strategy not found');
    }
    
  }

  public function getdepartments(){
    return $this->departmentrepository->getdepartments();
  }

  public function save(){
    $this->validate([
        "title"=>"required",
    ]);
    
     if($this->id){
        
        $response = $this->programmerepository->updateprogramme($this->id, [
            "strategy_id"=>$this->strategy->id,
            "title"=>$this->title,
            "code"=>$this->code,
            "updatedby"=>Auth::user()->id
        ]);
        if($response['status']=="success")
        {
           $this->success($response['message']);
        }
        else{
          $this->error($response['message']);
        }
     }
     else{
        $response = $this->programmerepository->createprogramme([
            "strategy_id"=>$this->strategy->id,
            "title"=>$this->title,
            "code"=>$this->code,
            "createdby"=>Auth::user()->id
        ]);
        if($response['status']=="success")
        {
          $this->success($response['message']);
        }
        else{
          $this->error($response['message']);
        }
     }
     $this->reset([
        "title",
        "id"
        ]);
        $this->closeModal();
  }
  public function addindicator($id){
    $this->departmentoutput_id = $id;
    $this->indicatormodal = true;
  }
  public function headers():array{
    return [
        ['key'=>'code','label'=>'Code'],
        ['key'=>'title','label'=>'Title'],
        ['key'=>'status','label'=>'Status'],
        ['key'=>'action','label'=>'']
    ];
  }
  public function headersoutcome():array{
    return [
       
        ['key'=>'title','label'=>'Title'],
        ['key'=>'status','label'=>'Status'],
        ['key'=>'action','label'=>'']
    ];
  }
  public function deleteprogramme($id){
    $response = $this->programmerepository->deleteprogramme($id);
    if($response['status']=="success")
    {
      $this->success($response['message']);
    }
    else{
      $this->error($response['message']);
    }
  }
  public function approveprogramme($id){
    $response = $this->programmerepository->approveprogramme($id);
    if($response['status']=="success")
    {
      $this->success($response['message']);
    }
    else{
      $this->error($response['message']);
    }
  }
  public function unapproveprogramme($id){
    $response = $this->programmerepository->unapproveprogramme($id);
    if($response['status']=="success")
    {
      $this->success($response['message']);
    }
    else{
      $this->error($response['message']);
    }
  }
  public function openModal(){
    $this->modal = true;
  }

  public function closeModal(){
    $this->modal = false;
  }
  public function getprogramme($id){
    $this->id = $id;
    $programme = $this->programmerepository->getprogramme($id);
    if($programme){
      $this->title = $programme->title;
      $this->code = $programme->code;
    }
    $this->openModal();
  }
  
  public function openViewModal($id){
    $this->programme_id = $id;
    $this->outcomemodal = true; 
  
  }
  public function closeViewModal(){
    $this->viewModal = false;
  }

 

  public function saveoutcome(){
        $data = [
            'title'=>$this->title,
            "createdby"=>Auth::user()->id,
            'programme_id'=>$this->programme_id,
        ];
        if($this->outcome_id){
            $data['updatedby'] = Auth::user()->id;
           $response = $this->outcomerepository->updateoutcome($this->outcome_id,$data);
           if($response['status']=='success'){
      
            $this->success($response['message']);
           }
           else{
            $this->error($response['message']);
           }
        }
        else{
            $response = $this->outcomerepository->createoutcome($data);
            if($response['status']=='success'){
              
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->reset([
            'title',
            'id'
            ]);
    }

    public function editoutcome($id){
        $this->outcome_id = $id;
        $outcome = $this->outcomerepository->getoutcome($id);
        if($outcome){
            $this->title = $outcome->title;
            $this->outcomemodal = true;
        }
    }
    public function deleteoutcome($id){
        $response = $this->outcomerepository->deleteoutcome($id);
        if($response['status']=='success'){
         
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function addoutput($id){
        $this->outcome_id = $id;
        $this->outputmodal = true;
    }
    public function editoutput($id){
        $this->output_id = $id;
        $output = $this->outputrepository->getoutput($id);
        if($output){
            $this->title = $output->title;
            $this->outputmodal = true;
        }
    }
    public function deleteoutput($id){
        $response = $this->outputrepository->deleteoutput($id);
        if($response['status']=='success'){
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function saveoutput(){
        $this->validate([
            'title'=>'required',
        ]);
        $data = [
            'title'=>$this->title,
            'outcome_id'=>$this->outcome_id
        ];
        if($this->output_id){
            $data['updatedby'] = Auth::user()->id;
            $response = $this->outputrepository->updateoutput($this->output_id,$data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }else{
                $this->error($response['message']);
            }
        }
        else{
            $response = $this->outputrepository->createoutput($data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->reset([
            'title',
            'id'
        ]);
        $this->outputmodal = false;
    }
    public function assignsubprogramme($id){
        $this->output_id = $id;
        $this->adddepartmentoutputmodal = true;
    }
    public function editsubprogramme($id){
        $this->departmentoutput_id = $id;
        $departmentoutput = $this->outputrepository->getdepartmentoutput($id);
       
        if($departmentoutput){
            $this->department_id = $departmentoutput->department_id;
            $this->weightage = (float)$departmentoutput->weightage;
            $this->output_id = $departmentoutput->output_id;
            $this->adddepartmentoutputmodal = true;
        }
    }
    public function deletesubprogramme($id){
        $response = $this->outputrepository->deletedepartmentoutput($id);
        if($response['status']=='success'){
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function saveassignsubprogramme(){
        $this->validate([
            'department_id'=>'required',
            'weightage'=>'required',
        ]);
        $data = [
            'output_id'=>$this->output_id,
            'department_id'=>$this->department_id,
            'weightage'=>$this->weightage,
            'createdby'=>Auth::user()->id,
        ];
        if($this->departmentoutput_id){
            $data['createdby'] = Auth::user()->id;
            $response = $this->outputrepository->updatedepartmentoutput($this->departmentoutput_id,$data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        else{
            $response = $this->outputrepository->adddepartmentoutput($data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }else{
                $this->error($response['message']);
            }
        }
        $this->reset([
            'department_id',
            'weightage',
            'id'
        ]);
        $this->addsubprogrammemodal = false;
    }
 

    public function saveindicator(){
        $this->validate([
            'title'=>'required',
            'uom'=>'required',
        ]);
        $data = [
            'title'=>$this->title,
            'uom'=>$this->uom,
            'departmentoutput_id'=>$this->departmentoutput_id,
            'createdby'=>Auth::user()->id,
        ];
        if($this->indicator_id){
            $data['updatedby'] = Auth::user()->id;
            $response = $this->indicatorrepository->updateindicator($this->indicator_id,$data);
            if($response['status']=='success'){
              
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        else{
            $response = $this->indicatorrepository->createindicator($data);
            if($response['status']=='success'){
            
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
    }
    public function editindicator($id){
        $this->indicator_id = $id;
        $indicator = $this->indicatorrepository->getindicator($id);
        if($indicator){
            $this->title = $indicator->title;
            $this->uom = $indicator->uom;
            $this->indicatormodal = true;
        }
    }

    public function addtarget($id){
        $this->indicator_id = $id;
        $this->targetmodal = true;
    }
    public function edittarget($id){
        $this->target_id = $id;
        $target = $this->indicatorrepository->gettarget($id);
        if($target){
            $this->target = $target->target;
            $this->year = $target->year;
            $this->indicator_id = $target->indicator_id;
            $this->variance = $target->variance;
            $this->targetmodal = true;
        }
    }
    public function deletetarget($id){
        $response = $this->indicatorrepository->deletetarget($id);
        if($response['status']=='success'){
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function savetarget(){
        $this->validate([
            'target'=>'required',
            'variance'=>'required',
            'year'=>'required',
        ]);
        $data = [
            'indicator_id'=>$this->indicator_id,
            'target'=>$this->target,
            'variance'=>$this->variance,
            'year'=>$this->year,
            'createdby'=>Auth::user()->id,
        ];
        if($this->target_id){
            $data['createdby'] = Auth::user()->id;
            $response = $this->indicatorrepository->updatetarget($this->target_id,$data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        else{
            $response = $this->indicatorrepository->addtarget($data);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->reset([
            'target',
            'variance',
            'id'
        ]);
        $this->targetmodal = false;
    }
        public function subprogrammeheaders():array{
            return [
                ['key'=>'department.name','label'=>'Department'],
                ['key'=>'weightage','label'=>'Weightage'],
                ['key'=>'action','label'=>'']
            ];
        }
    public function indicatorheaders():array{
        return [
            ['key'=>'title','label'=>'Title'],
            ['key'=>'uom','label'=>'UOM'],
            ['key'=>'action','label'=>'']
        ];
    }

    public function targetheaders():array{
        return [
            ['key'=>'year','label'=>'Year'],
            ['key'=>'target','label'=>'Target'],
            ['key'=>'variance','label'=>'Allowable Variance'],
            ['key'=>'action','label'=>'']
        ];
    }
    public function render()
    {
        return view('livewire.admin.management.strategydetail',[
            'headers'=>$this->headers(),
            'headersoutcome'=>$this->headersoutcome(),
            'headerssubprogramme'=>$this->subprogrammeheaders(),
            'headersindicator'=>$this->indicatorheaders(),
            'headerstarget'=>$this->targetheaders(),
            'departments'=>$this->getdepartments()
        ]);
    }
}
