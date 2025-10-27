<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iuserInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Departments extends Component
{
    use Toast;
    public $name;
    public $level;
    public $id;
    public $position;
    public $isprimary;
    public $reportto;
    public $user_id;
    public $department_id;
    public $user;
    public $departmentusers;
    public $department;
    public bool $modal = false;
    public $usermodal = false;
    public $addusermodal = false;
    public $departmentuser_id;
    public $breadcrumbs = [];
    protected $repo;
    protected $userrepo;
    public function mount(){
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Departments']
        ];
        $this->departmentusers = new Collection();
    }
    public function boot(idepartmentInterface $repo,iuserInterface $userrepo)
    {
        $this->repo = $repo;
        $this->userrepo = $userrepo;
    }
    public function getdepartments(){
        return $this->repo->getdepartments();
    }

    public function getallusers(){
        $users= $this->userrepo->getall();
        if($this->user_id){
           return $users->where('id','!=',$this->user_id);
        }
        return $users;
    }
    public function getdepartment($id){
        $this->id = $id;
        $this->department = $this->repo->getdepartment($id);
     
    }
    public function edit($id){  
       $this->getdepartment($id);
       $this->name = $this->department->name;
       $this->level = $this->department->level;
        $this->modal = true;
    }
    public function save(){
      $this->validate([
        "name"=>"required",
        "level"=>"required",
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['id','name','level']);
    }
    public function create(){
       $response = $this->repo->create([
        "name"=>$this->name,
        "level"=>$this->level,
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function update(){
        $response = $this->repo->update($this->id,[
            "name"=>$this->name,
            "level"=>$this->level,
            ]);
            if($response['status'] == "success"){
                $this->success($response['message']);
            }else{
                $this->error($response['message']);
            }

    }
    public function delete($id){
        $response = $this->repo->delete($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function getusers($id){
        $this->id = $id;
        $this->departmentusers = $this->repo->getusers($id);
        $this->usermodal = true;
    }
    public function  getuser($id){
        $this->departmentuser_id = $id;
        $this->user = $this->repo->getuser($id);
        $this->user_id = $this->user->user_id;
        $this->position = $this->user->position;
        $this->isprimary = $this->user->isprimary;
        $this->reportto = $this->user->reportto;
       
    }
    public function edituser($id){
        $this->getuser($id);
        $this->addusermodal = true;
    }

    public  function saveuser(){
        $this->validate([
            "user_id"=>"required",
            "position"=>"required",
            "isprimary"=>"required",
            "reportto"=>"required",
        ]);
        if($this->departmentuser_id){
            $this->updateuser();
        }else{
            $this->createuser();
        }
        $this->reset(['user_id','position','isprimary','reportto']);
        $this->addusermodal = false;
    }
    public function createuser(){
        $this->validate([
            "user_id"=>"required",
            "position"=>"required",
            "isprimary"=>"required",
            "reportto"=>"required",
        ]);
        $response = $this->repo->createuser([
            "user_id"=>$this->user_id,
            "position"=>$this->position,
            "isprimary"=>$this->isprimary,
            "reportto"=>$this->reportto,
            "department_id"=>$this->id,
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
            $this->refreshdepartmentusers();
        }else{
            $this->error($response['message']);
        }
    }
    public function updateuser(){
        $this->validate([
            "user_id"=>"required",
            "position"=>"required",
            "isprimary"=>"required",
            "reportto"=>"required",
        ]);
        $response = $this->repo->updateuser($this->departmentuser_id,[
            "user_id"=>$this->user_id,
            "position"=>$this->position,
            "isprimary"=>$this->isprimary,
            "reportto"=>$this->reportto,
        ]);
        if($response['status'] == "success"){
            $this->refreshdepartmentusers();
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }
    public function deleteuser($id){
        $response = $this->repo->deleteuser($id);
        if($response['status'] == "success"){
            $this->refreshdepartmentusers();
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function refreshdepartmentusers(){
        $this->departmentusers = $this->repo->getusers($this->id);
    }
    public function headers():array{
        return [
            ["key"=>"name","label"=>"Name"],
            ["key"=>"level","label"=>"Level"],
            ["key"=>"users","label"=>"Users"]
        ];
    }
    
    public function render()
    {
        return view('livewire.admin.configuration.departments',[
            "departments"=>$this->getdepartments(),
            "headers"=>$this->headers(),
            "users"=>$this->getallusers(),
        ]);
    }
}
