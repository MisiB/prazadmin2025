<?php

namespace App\Livewire\Admin\Finance\Budgetconfigurations\Components;

use App\Interfaces\repositories\ibudgetconfigurationInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Sourceoffunds extends Component
{
    use Toast;
    protected $repo;
    public $name;
    public $id;
    public $modal = false;
    public function boot(ibudgetconfigurationInterface $repo)
    {
        $this->repo = $repo;
   
    }

    public function getdata()
    {
        return $this->repo->getsourceoffunds();
    }

    public function headers():array{
        return [
            ["key"=>"name", "label"=>"Name"],
            ["key"=>"action", "label"=>""]
        ];
    }
    public function  edit($id){
        $this->id = $id;
         $record =$this->repo->getsourceoffund($id);
         $this->name = $record->name;
         $this->modal = true;
    }
    
    public function save(){
        $this->validate([
            "name"=>"required"
        ]);
        if($this->id){
           $this->update();
        }else{
            $this->create();
        }
        $this->reset([
            "name",
            "id"
            ]);
    }
    public function create(){
        $response = $this->repo->createsourceoffund(["name"=>$this->name]);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function update(){
        $response = $this->repo->updatesourceoffund($this->id, ["name"=>$this->name]);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function delete($id){
        $response = $this->repo->deletesourceoffund($id);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetconfigurations.components.sourceoffunds',['data'=>$this->getdata(), 'headers'=>$this->headers()]);
    }
}
