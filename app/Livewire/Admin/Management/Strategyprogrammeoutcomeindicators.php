<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\istrategyInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Strategyprogrammeoutcomeindicators extends Component
{
    use Toast;
    public $uuid,$programme_id,$outcome_id;
    public $id;
    public $indicator;
    public $target;
    public $uom;
    public $variance;
    public $varianceuom;
    protected $repo;
    protected $departmentrepo;
    public $breadcrumbs=[];
    public $outcome;
    public $modal=false;
    public $subprogrammeModal=false;
    public $addsubprogrammeModal=false;
    public $indicator_id;
    public  $subprogrammes;
    public $subprogramme_id;
    public $department_id;
    public $weightage;
    public function mount($uuid,$programme_id,$outcome_id){
        $this->uuid=$uuid;
        $this->programme_id=$programme_id;
        $this->outcome_id=$outcome_id;
        $this->getoutcome();
        $this->breadcrumbs=[
            [
                'label' => 'Home',
                'link' => route('admin.home'),
            ],
            [
                'label' => $this->outcome?->programme?->strategy?->name,
                'link' => route('admin.management.strategies'),
            ],
            [
                'label' => $this->outcome?->programme?->title,
                'link' => route('admin.management.strategydetail',[$this->uuid]),
            ],
            [
                'label' => $this->outcome?->title,
                'link' => route('admin.management.strategyprogrammeoutcomes',[$this->uuid,$this->programme_id]),
            ],
            [
                'label' => 'Programme Outcome Indicators',
            ],
        ];
        $this->subprogrammes = new Collection();
    }

    public function boot(istrategyInterface $repo,idepartmentInterface $departmentrepo){
        $this->repo=$repo;
        $this->departmentrepo=$departmentrepo;
    }
 
    public function getoutcome(){
        $outcome = $this->repo->getprogrammeoutcomebyuuid($this->programme_id,$this->outcome_id);
        if($outcome){
            if($outcome->programme->strategy->uuid!=$this->uuid){
                return redirect()->route('admin.home');
            }
            $this->outcome = $outcome;
        }   
        else{
         return redirect()->route('admin.home');
        }
    }

    public function getdepartments(){
        return $this->departmentrepo->getdepartments();
    }
    public function openModal(){
        $this->modal=true;
    }
    public function closeModal(){
        $this->modal=false;
    }

    public function save(){
        $this->validate([
            'indicator'=>'required',
            'target'=>'required',
            'uom'=>'required',
            'variance'=>'required',
            'varianceuom'=>'required',
        ]);
        if($this->id){
           $response= $this->repo->updateprogrammeoutcomeindicator($this->id,[
                'indicator'=>$this->indicator,
                'target'=>$this->target,
                'uom'=>$this->uom,
                'variance'=>$this->variance,
                'varianceuom'=>$this->varianceuom,
                'updatedby'=>Auth::user()->id
            ]);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        else{
            $response= $this->repo->createprogrammeoutcomeindicator([
                'programmeoutcome_id'=>$this->outcome_id,
                'indicator'=>$this->indicator,
                'target'=>$this->target,
                'uom'=>$this->uom,
                'variance'=>$this->variance,
                'varianceuom'=>$this->varianceuom,
                "createdby"=>Auth::user()->id
            ]);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->closeModal();
    }
  
    public function getindicator($id){
        $indicator = $this->repo->getprogrammeoutcomeindicator($id);
        if($indicator){
            $this->id = $indicator->id;
            $this->indicator = $indicator->indicator;
            $this->target = $indicator->target;
            $this->uom = $indicator->uom;
            $this->variance = $indicator->variance;
            $this->varianceuom = $indicator->varianceuom;
            $this->openModal();
        }
    }
    public function deleteindicator($id){
       $response = $this->repo->deleteprogrammeoutcomeindicator($id);
       if($response['status']=="success"){
        $this->success($response['message']);
       }
       else{
        $this->error($response['message']);
       }
    }


    public function openSubprogrammeModal($indicator_id){
        $this->indicator_id = $indicator_id;
        $this->subprogrammes = $this->repo->getprogrammeoutcomeindicator($indicator_id)->subprogrammes;
        $this->subprogrammeModal = true;
    }

    public function saveSubprogramme(){
        $this->validate([
            'department_id'=>'required',
            'weightage'=>'required',
        ]);
        if($this->subprogramme_id){
            $response = $this->repo->updatesubprogramme($this->subprogramme_id,[
                'department_id'=>$this->department_id,
                'programmeoutcomeindicator_id'=>$this->indicator_id,
                'weightage'=>$this->weightage,
                'updatedby'=>Auth::user()->id
            ]);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        else{
            $response = $this->repo->createsubprogramme([
                'programmeoutcomeindicator_id'=>$this->indicator_id,
                'department_id'=>$this->department_id,
                'weightage'=>$this->weightage,
                "createdby"=>Auth::user()->id
            ]);
            if($response['status']=='success'){
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->subprogrammes = $this->repo->getprogrammeoutcomeindicator($this->indicator_id)->subprogrammes;
        $this->reset(['department_id','weightage']);
    }
    public function closeSubprogrammeModal(){
        $this->subprogrammeModal=false;
        $this->department_id=null;
        $this->weightage=null;
    }
    public function getsubprogramme($id){
        $subprogramme = $this->repo->getsubprogramme($id);
        if($subprogramme){
            $this->subprogramme_id = $subprogramme->id;
            $this->department_id = $subprogramme->department_id;
            $this->weightage = $subprogramme->weightage;
            $this->addsubprogrammeModal=true;
        }
    }
    public function deletesubprogramme($id){
        $response = $this->repo->deletesubprogramme($id);
        if($response['status']=="success"){
            $this->subprogrammes = $this->repo->getprogrammeoutcomeindicator($this->indicator_id)->subprogrammes;
 
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function render()
    {
        return view('livewire.admin.management.strategyprogrammeoutcomeindicators',[
            'departments'=>$this->getdepartments()
        ]);
    }
}
