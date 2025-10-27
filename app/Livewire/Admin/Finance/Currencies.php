<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\icurrencyInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Currencies extends Component
{
    use Toast;
    public $name;
    public $id;
    public $status;
    public $modal = false;
    protected $repo;
    public $breadcrumbs;
    public function boot(icurrencyInterface $repo)
    {
        $this->repo = $repo;
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Currencies']
        ];
    }
    public function getcurrencies()
    {
        $response = $this->repo->getcurrencies();
        return $response;
    }
    public  function edit($id){
        $response = $this->repo->getcurrency($id);
        $this->id = $id;
        $this->name = $response->name;
        $this->status = $response->status;
        $this->modal = true;
    }

    public function save(){
        $this->validate([
            'name' => 'required',
            'status' => 'required'
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['name', 'status', 'id']);
    }

    public  function create(){
       $response = $this->repo->createcurrency([
            'name' => $this->name,
            'status' => $this->status
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function update(){
        $response = $this->repo->updatecurrency($this->id, [
            'name' => $this->name,
            'status' => $this->status
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function delete($id){
        $response = $this->repo->deletecurrency($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public  function headers():array    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'status', 'label' => 'Status'],
        ];
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['name', 'status', 'id']);
    }

    public function render()
    {
        return view('livewire.admin.finance.currencies',[
            "rows" => $this->getcurrencies(),
            "headers" => $this->headers()
        ]);
    }
}
