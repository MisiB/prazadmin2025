<?php

namespace App\Livewire\Admin\Workflows\Configurations\Components;

use App\Interfaces\repositories\ipermissionInterface;
use App\Interfaces\repositories\iworkflowInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Parameters extends Component
{
    use Toast;
    public $workflow;
    public $modal = false;
    public $newmodal = false;
    public  $parameters;
    protected  $workflowrepo;
    protected  $permissionrepo;
    public $order;
    public $status;
    public $name;
    public $permission_id;
    public $id;
    public function boot(iworkflowInterface $workflowrepo,ipermissionInterface $permissionrepo){
        $this->workflowrepo = $workflowrepo;
        $this->permissionrepo = $permissionrepo;
    }
    public function mount($workflow){
        $this->workflow = $workflow;
        $this->parameters = new Collection();
    }
    public function getpermissions(){
        return $this->permissionrepo->getpermissions();
    }
    public function getparameters(){
        $this->getdata();
        $this->modal = true;
    }
    public function getdata(){
        $this->parameters = $this->workflowrepo->getworkflowparameters($this->workflow->id);
    }



    public function saveparameter(){
        $this->validate([
            "order"=>"required",
            "status"=>"required",
            "name"=>'required',
            "permission_id"=>"required"
        ]);
        if($this->id){
            $this->updateparameter();
        }else{
            $this->createparameter();
        }
        $this->reset(["order","status","permission_id","id"]);
    }
    public function createparameter(){
       $response = $this->workflowrepo->createworkflowparameter([
            "order"=>$this->order,
            "name"=>$this->name,
            "status"=>$this->status,
            "permission_id"=>$this->permission_id,
            "workflow_id"=>$this->workflow->id
        ]);
        if($response["status"]=="success"){
            $this->getdata();
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function updateparameter(){
       $response = $this->workflowrepo->updateworkflowparameter($this->id,[
            "order"=>$this->order,
            "status"=>$this->status,
            "name"=>$this->name,
            "permission_id"=>$this->permission_id,
            "workflow_id"=>$this->workflow->id
        ]);
        if($response["status"]=="success"){
            $this->getdata();
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }   

    public function edit($id){
        $this->id = $id;
        $parameter = $this->workflowrepo->getworkflowparameter($id);
        $this->order = $parameter->order;
        $this->status = $parameter->status;
        $this->name = $parameter->name;
        $this->permission_id = $parameter->permission_id;
        $this->newmodal = true;
    }
    public function deleteparameter($id){
        $response = $this->workflowrepo->deleteworkflowparameter($id);
        if($response["status"]=="success"){
            $this->getdata();
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function headers():array{
        return [
            ["key"=>"order","label"=>"Order"],
            ["key"=>"name","label"=>"Name"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"permission.name","label"=>"Permission"],
            ["key"=>"actions","label"=>"Actions"]
        ];
    }
    public function render()
    {
        return view('livewire.admin.workflows.configurations.components.parameters',[
            "permissions"=>$this->getpermissions(),
            "headers"=>$this->headers()
        ]);
    }
}
