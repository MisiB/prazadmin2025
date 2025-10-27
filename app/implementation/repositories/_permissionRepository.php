<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ipermissionInterface;
use Spatie\Permission\Models\Permission;

class _permissionRepository implements ipermissionInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Permission $model)
    {
        $this->model = $model;
    }
    public function getpermissions(){
        return $this->model::all();
    }
    public function getpermission(int $id){
        try {
            $permission = $this->model::find($id);
            return $permission ? $permission : ["status" => "error", "message" => "Permission not found."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function createpermission(array $permission){
        try {
            $exists = $this->model->where('name', $permission['name'])->first();
            if (!$exists) {
                $this->model->create($permission);
                return ['status' => 'success', 'message' => 'Permission created successfully.'];
            }
         
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function updatepermission(int $id, array $permission){
        try {
            $permission = $this->model::where('id', $id)->update($permission);
            if (!$permission) {
                return ['status' => 'error', 'message' => 'Permission not found.'];
            }
            return ['status' => 'success', 'message' => 'Permission updated successfully.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function deletepermission(int $id){
        try {
            $permission = $this->model::find($id);
            if (!$permission) {
                return ['status' => 'error', 'message' => 'Permission not found.'];
            }
            $permission->delete();
            return ['status' => 'success', 'message' => 'Permission deleted successfully.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
