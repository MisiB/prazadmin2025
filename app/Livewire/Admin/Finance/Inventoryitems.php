<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\iinventoryitemInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Inventoryitems extends Component
{
    use Toast;
    public $code;
    public $name;
    public $type;
    public $refundable;
    public $requiretender;
    public $id;
    public $modal = false;
    protected $repo;
    public $breadcrumbs;
    public function boot(iinventoryitemInterface $repo)
    {
        $this->repo = $repo;
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Inventories']
        ];
    }
    public function getinventories()
    {
        $response = $this->repo->getinventories();
        return $response;
    }
    public  function edit($id){
        $response = $this->repo->getinventory($id);
        $this->id = $id;
        $this->code = $response->code;
        $this->name = $response->name;
        $this->type = $response->type;
        $this->refundable = $response->refundable;
        $this->requiretender = $response->requiretender;

        $this->modal = true;
    }

    public function save(){
        $this->validate([
            'code' => 'required',
            'name' => 'required',
            'type' => 'required',
            'refundable' => 'required',
            'requiretender' => 'required',
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['code', 'name', 'type', 'refundable', 'requiretender', 'id']);
    }

    public  function create(){
       $response = $this->repo->createinventory([
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'refundable' => $this->refundable,
            'requiretender' => $this->requiretender
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function update(){
        $response = $this->repo->updateinventory($this->id, [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'refundable' => $this->refundable,
            'requiretender' => $this->requiretender
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function delete($id){
        $response = $this->repo->deleteinventory($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public  function headers():array    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'refundable', 'label' => 'Refundable'],
            ['key' => 'requiretender', 'label' => 'Require Tender'],
        ];
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['code', 'name', 'type', 'refundable', 'requiretender', 'id']);
    }


    public function render()
    {
        return view('livewire.admin.finance.inventoryitems', [
            "rows" => $this->getinventories(),
            "headers" => $this->headers()
        ]);
    }
}
 