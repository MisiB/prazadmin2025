<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iroleRepository;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class _roleRepository implements iroleRepository
{
    /**
     * Create a new class instance.
     */

     protected $model;
     protected $permission;
    public function __construct(Role $model, Permission $permission)
    {
        $this->model = $model;
        $this->permission = $permission;
    }
    public function getroles(){
        try {
            return $this->model->all();
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function getrole(int $id):?Role{
        try {
            return $this->model->with('permissions')->where('id', $id)->first();
        } catch (\Exception $e) {
            return null;
        }
    }
    public function getusersbyrole($rolename){
        try {
            return $this->model->where('name', $rolename)->first()->users;
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function createrole(array $role){
        try {
            return $this->model->create($role);
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function updaterole(int $id, array $role){
        try {
            return $this->model->find($id)->update($role);
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function deleterole(int $id){
        try {
            return $this->model->find($id)->delete();
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public  function assignpermissions(int $id, array $permissions){
        try {
            $retrievedpermissions = $this->permission->whereIn('id', $permissions)->get()->pluck('name')->toArray();
            $this->model->find($id)->syncPermissions($retrievedpermissions);
            return ["status" => "success", "message" => "Permissions assigned successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }   
}
