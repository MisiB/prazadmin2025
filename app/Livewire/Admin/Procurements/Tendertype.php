<?php

namespace App\Livewire\Admin\Procurements;

use App\Interfaces\repositories\itenderInterface;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Tendertype extends Component
{
    use WithPagination,Toast;
    protected $repo;
    public $name;
    public $id;
    public $modal = false;
    public $breadcrumbs = [];

    public function mount(){
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Tender Types']
        ];
    }
    public function boot(itenderInterface $repo)
    {
        $this->repo = $repo;
    }

    public function gettypes(){
        return $this->repo->gettendertypes();
    }
    public function headers():array{
        return [
            ['key'=>'name','label'=>'Name']
        ];
    }
    public function save(){
      $this->validate([
        'name'=>'required'
      ]);
      if($this->id){
        $this->update();
      }else{
        $this->create();
      }
      $this->reset('name','id');
    }

    public function create(){
      $response = $this->repo->createtendertype([
        'name'=>$this->name
      ]);
      if($response['status']=="success"){
         $this->success($response['message']);
      }else{
        $this->error($response['message']);
      }
    }
    public function edit($id){
      $this->id = $id;
      $this->name = $this->repo->gettendertype($id)->name;
    }
   
    public function update(){
      $response = $this->repo->updatetendertype($this->id, [
        'name'=>$this->name
      ]);
      if($response['status']=="success"){
         $this->success($response['message']);
      }else{
        $this->error($response['message']);
      }
    }
    public function delete($id){
      $response = $this->repo->deletetendertype($id);
      if($response['status']=="success"){
         $this->success($response['message']);
      }else{
        $this->error($response['message']);
      }
    }
    public function render()
    {
        return view('livewire.admin.procurements.tendertype',[
            'types'=>$this->gettypes(),
            'headers'=>$this->headers()
        ]);
    }
}
