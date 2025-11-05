<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iexchangerateInterface;
use App\Models\Exchangerate;

class _exchangerateRepository implements iexchangerateInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Exchangerate $model)
    {
        $this->model = $model;
    }

    public function getexchangerates()
    {
        return $this->model->with(['primarycurrency', 'secondarycurrency','user'])->get();
    }

    public function getexchangerate($id)
    {
        return $this->model->find($id);
    }
    public function getexchangeratebycurrency($currency_id){
        return $this->model->where('secondary_currency_id', $currency_id)->latest()->first();
    }
    public function getexchangeratesbyprimarycurrency($currency_id){
        return $this->model->with(['primarycurrency', 'secondarycurrency'])->where('secondary_currency_id', $currency_id)->orderBy('created_at', 'desc')->get();
    }
    public function getlatestexchangerate($currency_id=null){
        $currency_id = $currency_id == 'null'? config('generalsettings.defaultcurrency') : $currency_id;
      
        
        return $this->model->with(['primarycurrency', 'secondarycurrency'])->where('secondary_currency_id', $currency_id)->latest()->first();
    }

    public function createexchangerate($data)
    {
        try {
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Exchangerate created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function updateexchangerate($id, $data)
    {
        try {
            $this->model->find($id)->update($data);
            return ['status' => "success", 'message' => 'Exchangerate updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function deleteexchangerate($id)
    {
        try {
            $this->model->find($id)->delete();
            return ['status' => "success", 'message' => 'Exchangerate deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}