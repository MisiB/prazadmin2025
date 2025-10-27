<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\iworkflowInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Configurations extends Component
{
    use Toast;
    public $name;
    public $id;
    public $description;
    public bool $modal = false;
    public $breadcrumbs =[];
    protected $workflowrepo;

    public function mount(){
        $this->breadcrumbs = [            
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Workflows']
        ];
    }
    public function boot(iworkflowInterface $workflowrepo){
        $this->workflowrepo = $workflowrepo;
    }

    public function getworkflows(){
        return $this->workflowrepo->getworkflows();
    }

    public function edit($id){
    
        $workflow = $this->workflowrepo->getworkflow($id);
        $this->name = $workflow->name;
        $this->description = $workflow->description;
        $this->id = $id;
        $this->modal = true;
    }

    public function headers():array{
        return [
            ["key"=>"name","label"=>"Name"],
            ["key"=>"description","label"=>"Description"],
            ["key"=>"actions","label"=>""]
        ];
    }

    public function save(){
            try{
        $this->validate([
            "name"=>"required",
            "description"=>"required"
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(["name","description","id"]);
    }catch(\Exception $e){
        $this->error($e->getMessage());
    }
}

    public function create(){
       $response= $this->workflowrepo->createworkflow([
            "name"=>$this->name,
            "description"=>$this->description
        ]);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function update(){
       $response = $this->workflowrepo->updateworkflow($this->id,[
            "name"=>$this->name,
            "description"=>$this->description
        ]);
        if($response["status"]=="success"){
            $this->success($response["message"]);
            $this->modal = false;
        }else{
            $this->error($response["message"]);
        }
    }

    public function delete($id){
        $response= $this->workflowrepo->deleteworkflow($id);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    
    public function render()
    {
        return view('livewire.admin.workflows.configurations',[
            "workflows"=>$this->getworkflows(),
            "headers"=>$this->headers()
        ]);
    }
}
