<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Models\Bankaccount;

class _bankaccountRepository implements ibankaccountInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;

    public function __construct(Bankaccount $bankaccount)
    {
        $this->model = $bankaccount;
    }

    public function getbankaccounts()
    {
        return $this->model->all();
    }

    public function getbankaccount($id)
    {
        return $this->model->find($id);
    }

    public function getbankaccountbytype($currency_id, $type)
    {
        return $this->model->where('currency_id', $currency_id)->where('account_type', $type)->first();
    }

    public function retrievebankaccount($bank_id, $type, $currency_id)
    {
        return $this->model->where('bank_id', $bank_id)->where('account_type', $type)->where('currency_id', $currency_id)->first();
    }

    public function createbankaccount($data)
    {
        try {
            $check = $this->model->where('account_number', $data['account_number'])->where('bank_id', $data['bank_id'])->first();
            if ($check) {
                return ['status' => 'error', 'message' => 'Bank account already exists'];
            }
            $this->model->create($data);

            return ['status' => 'success', 'message' => 'Bank account created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to create bank account: '.$e->getMessage()];
        }
    }

    public function updatebankaccount($id, $data)
    {
        try {
            $this->model->find($id)->update($data);

            return ['status' => 'success', 'message' => 'Bank account updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to update bank account: '.$e->getMessage()];
        }
    }

    public function deletebankaccount($id)
    {
        try {
            $this->model->find($id)->delete();

            return ['status' => 'success', 'message' => 'Bank account deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to delete bank account: '.$e->getMessage()];
        }
    }

    public function getbankaccountsByBank($bank_id)
    {
        try {
            return $this->model->with('currency')->where('bank_id', $bank_id)->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to get bank accounts: '.$e->getMessage()];
        }
    }

    public function getBankAccountByBankIdAndAccountNumber($bank_id, $account_number)
    {
        return $this->model->where('account_number', $account_number)->first();
    }
}
