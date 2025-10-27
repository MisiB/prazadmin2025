<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\icurrencyInterface;
use App\Models\Currency;

class _currencyRepository implements icurrencyInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Currency $model)
    {
        $this->model = $model;
    }

    public function getcurrencies(){
        return $this->model->all();
    }

    public function getcurrency($id){
        return $this->model->find($id);
    }

    public function getCurrencyByCode($code){
        return $this->model->where('name', $code)->first();
    }

    public function createcurrency($data){
        try {
        $exist = $this->model->where('name', $data['name'])->first();
        if($exist){
            return ['status' => "error", 'message' => 'Currency already exists'];
        }
        $this->model->create($data);
        return ['status' => "success", 'message' => 'Currency created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function updatecurrency($id, $data){
            try {
            $exist = $this->model->where('name', $data['name'])->first();
            if($exist && $exist->id != $id){
                return ['status' => "error", 'message' => 'Currency already exists'];
            }
            $this->model->find($id)->update($data);
            return ['status' => "success", 'message' => 'Currency updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function deletecurrency($id){
        try {
            $this->model->find($id)->delete();
            return ['status' => "success", 'message' => 'Currency deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}
