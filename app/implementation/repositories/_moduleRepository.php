<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\imoduleInterface;
use App\Models\Module;

class _moduleRepository implements imoduleInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Module $model)
    {
        $this->model = $model;
    }
    public function getmodules(){
        try {
            return $this->model->with('submodules.permissions.roles')->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getmodule(int $id){
        try {
            return $this->model->with('submodules')->find($id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createmodule(array $module){
        try {
            $exists = $this->model->where('name', $module['name'])->first();
            if ($exists) {
                return ["status" => "error", "message" => "Module already exists."];
            }
            $this->model->create($module);
            return ["status" => "success", "message" => "Module created successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function updatemodule(int $id, array $module){
        try {
            $module = $this->model->where('id', $id)->update($module);
            if (!$module) {
                return ["status" => "error", "message" => "Module not found."];
            }
            return ["status" => "success", "message" => "Module updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }

    }
    public function deletemodule(int $id){
        try {
            $module = $this->model::find($id);
            if (!$module) {
                return ["status" => "error", "message" => "Module not found."];
            }
            $module->delete();
            return ["status" => "success", "message" => "Module deleted successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
