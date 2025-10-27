<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iaccounttypeInterface;
use App\Models\Accounttype;

class _accounttypeRepository implements iaccounttypeInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Accounttype $model)
    {
        $this->model = $model;
    }

    public function getaccounttypes()
    {
        try {
            return $this->model::get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getaccounttype(int $id)
    {
        try {
            $accounttype = $this->model::with('roles', 'modules')->where('id', $id)->first();
            return $accounttype ? $accounttype : ["status" => "error", "message" => "Account type not found"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function createaccounttype(array $accounttype)
    {
        try {
            $exists = $this->model::where('name', $accounttype['name'])->exists();
            if ($exists) {
                return ["status" => "error", "message" => "Account type already exists"];
            }
            $newAccounttype = $this->model::create($accounttype);
            return ["status" => "success", "message" => "Account type created successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function updateaccounttype(int $id, array $accounttype)
    {
        try {
            $exists = $this->model::where('id', $id)->first();
            if (!$exists) {
                return ["status" => "error", "message" => "Account type not found"];
            }
            $exists->update($accounttype);
            return ["status" => "success", "message" => "Account type updated successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function deleteaccounttype(int $id)
    {
        try {
            $accounttype = $this->model::find($id);
            if (!$accounttype) {
                return ["status" => "error", "message" => "Account type not found"];
            }
            $accounttype->delete();
            return ["status" => "success", "message" => "Account type deleted successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
