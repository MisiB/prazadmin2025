<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ipaynowintegrationsInterface;
use App\Models\Paynowintegration;

class _paynowintegrationsRepository implements ipaynowintegrationsInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Paynowintegration $model)
    {
        $this->model = $model;
    }


    public function getpaynowintegrations()
    {
        return $this->model->with('currency', 'bankaccount')->get();
    }

    public function getpaynowintegration($id)
    {
        return $this->model->find($id);
    }

    public function createpaynowintegration($data)
    {
        try {
            $exist = $this->model->where('key', $data['key'])->first();
            if ($exist) {
                return ['status' => "error", 'message' => 'Paynowintergration already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Paynowintergration created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function updatepaynowintegration($id, $data)
    {
        try {
            $exist = $this->model->where('key', $data['key'])->first();
            if ($exist && $exist->id != $id) {
                return ['status' => "error", 'message' => 'Paynowintergration already exists'];
            }
            $this->model->find($id)->update($data);
            return ['status' => "success", 'message' => 'Paynowintergration updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function deletepaynowintegration($id)
    {
        try {
            $this->model->find($id)->delete();
            return ['status' => "success", 'message' => 'Paynowintergration deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function getpaynowparameters($data){
       return  $this->model->where("type",$data["type"])->where("currency_id",$data["currency_id"])->first();
    }
}
