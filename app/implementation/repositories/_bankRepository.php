<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibankInterface;
use App\Models\Bank;

class _bankRepository implements ibankInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;

    public function __construct(Bank $bank)
    {
        $this->model = $bank;
    }

    public function getBanks()
    {
        return $this->model->all();
    }

    public function getBank($id)
    {
        return $this->model->find($id);
    }

    public function getBankBySalt($salt)
    {
        return $this->model->where('salt', $salt)->first();
    }

    public function getBankbytoken($token)
    {
        return $this->model->where('token', $token)->first();
    }

    public function createBank($data)
    {
        try {
            $check = $this->model->where('name', $data['name'])->first();
            if ($check) {
                return ['status' => 'error', 'message' => 'Bank already exists'];
            }
            $this->model->create($data);

            return ['status' => 'success', 'message' => 'Bank created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateBank($id, $data)
    {
        try {
            $bank = $this->model->where('id', '=', $id)->first();
            if ($bank == null) {
                return ['status' => 'error', 'message' => 'Bank  not found'];
            }
            $bank->update($data);
            $bank->save();

            return ['status' => 'success', 'message' => 'Bank successfully updated'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteBank($id)
    {
        try {
            $bank = $this->model->where('id', '=', $id)->first();
            if ($bank == null) {
                return ['status' => 'error', 'message' => 'Bank not found'];
            }
            $bank->delete();

            return ['status' => 'success', 'message' => 'Bank successfully deleted'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
