<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\isubmoduleInterface;
use App\Models\Submodule;

class _submoduleRepository implements isubmoduleInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Submodule $model)
    {
        $this->model = $model;
    }
    public function getsubmodule(int $id){
        try {
            return $this->model->with("permissions")->where('id', $id)->first();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createsubmodule(array $submodule){
        try {
            $exists = $this->model->where('name', $submodule['name'])->first();
            if ($exists) {
                return ["status" =>"error", "message" => "Submodule already exists."];
            }
            $this->model->create($submodule);
            return ["status" => "success", "message" => "Submodule created successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function updatesubmodule(int $id, array $submodule){
        try {
            $submodule = $this->model::where('id', $id)->update($submodule);
            if (!$submodule) {
                return ["status" => "error", "message" => "Submodule not found."];
            }
            return ["status" => "success", "message" => "Submodule updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function deletesubmodule(int $id){
        try {
            $submodule = $this->model::find($id);
            if (!$submodule) {
                return ["status" => "error", "message" => "Submodule not found."];
            }
            $submodule->delete();
            return ["status" => "success", "message" => "Submodule deleted successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
