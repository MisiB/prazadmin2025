<?php

namespace App\implementation\repositories;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iaccountsettingInterface;
use App\Models\Accountsetting;

class _accountsettingRepository implements iaccountsettingInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Accountsetting $model)
    {
        $this->model = $model;
    }

    public function getsettings()
    {
        try {
            return $this->model->first();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function create(array $data)
    {

        try {
            $setting = $this->model->first();
            if ($setting == null) {
                $this->model::create($data);
            } else {
                $setting->update($data);
            }
            return ['status' => "success", 'message' => 'Account settings updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function update(array $data)
    {
        try {
            $this->model->update($data);
            return ['status' => ApiResponse::SUCCESS->value, 'message' => 'Account settings updated successfully'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
