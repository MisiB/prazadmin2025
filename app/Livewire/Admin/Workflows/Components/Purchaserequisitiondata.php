<?php

namespace App\Livewire\Admin\Workflows\Components;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Purchaserequisitiondata extends Component 
{
    use Toast;
    public $uuid;
    public $modal;
    public $decision;
    public $comment;
    protected $repository;
    protected $authrepo;
    public $purchaserequisition_id;
    public $selectedTab="approval-tab";
    public $decisionmodal;
    public $approvalcode;
    public $showcode = false;
    public function mount($uuid){
        $this->uuid = $uuid;
    }
    public function boot(ipurchaseerequisitionInterface $repository,iauthInterface $authrepo){
        $this->repository = $repository;
        $this->authrepo = $authrepo;
    }

    public function getpurchaserequisition(){
        $purchaserequisition = $this->repository->getpurchaseerequisitionbyuuid($this->uuid);
        $this->purchaserequisition_id = $purchaserequisition->id;
        return $purchaserequisition;
    }
    public function recommend(){
        $this->validate([
            "decision"=>"required",
            "comment"=>"required_if:decision,REJECT",
            "approvalcode"=>"required"
        ]);
        $checkcode = $this->authrepo->checkapprovalcode($this->approvalcode);
        if($checkcode["status"]=="success"){
            $response = $this->repository->recommend($this->purchaserequisition_id,["decision"=>$this->decision,"comment"=>$this->comment]);
            if($response["status"]=="success"){
                $this->success($response["message"]);
                $this->modal = false;
            }else{
            $this->error($response["message"]);
        }
    }else{
        $this->error($checkcode["message"]);
    }
    }
    public function savedecision(){
        $this->validate([
            "decision"=>"required",
            "comment"=>"required_if:decision,REJECT",
            "approvalcode"=>"required"
        ]);
        $checkcode = $this->authrepo->checkapprovalcode($this->approvalcode);
        if($checkcode["status"]=="success"){
            $response = $this->repository->makedecision($this->purchaserequisition_id,["decision"=>$this->decision,"comment"=>$this->comment]);
            if($response["status"]=="success"){
                $this->success($response["message"]);
                $this->reset(['decision','comment','approvalcode']);
                $this->decisionmodal = false;
            }else{
            $this->error($response["message"]);
        }
    }else{
        $this->error($checkcode["message"]);
    }
    }
    public function render()
    {
        return view('livewire.admin.workflows.components.purchaserequisitiondata',[
            "purchaserequisition"=>$this->getpurchaserequisition()
        ]);
    }
}
