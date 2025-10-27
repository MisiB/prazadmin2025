<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itenderInterface;
use App\Models\Tender;
use App\Models\Tenderfee;
use App\Models\Tendertype;

class _tenderRepository implements itenderInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;

    protected $tendertype;

    protected $tenderfee;

    public function __construct(Tender $model, Tendertype $tendertype, Tenderfee $tenderfee)
    {
        $this->model = $model;
        $this->tendertype = $tendertype;
        $this->tenderfee = $tenderfee;
    }

    public function createtendertype(array $data)
    {
        try {
            $exist = $this->tendertype->where('name', $data['name'])->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Tendertype already exists'];
            }
            $this->tendertype->create([
                'name' => $data['name'],
            ]);

            return ['status' => 'success', 'message' => 'Tendertype created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function gettendertypes()
    {
        return $this->tendertype->all();
    }

    public function gettendertype($name)
    {
        return $this->tendertype->where('name', $name)->first();
    }

    public function updatetendertype($id, array $data)
    {
        try {
            $exist = $this->tendertype->where('name', $data['name'])->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Tendertype already exists'];
            }
            $this->tendertype->where('id', $id)->update([
                'name' => $data['name'],
            ]);

            return ['status' => 'success', 'message' => 'Tendertype updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetendertype($id)
    {
        try {
            $this->tendertype->where('id', $id)->delete();

            return ['status' => 'success', 'message' => 'Tendertype deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function create(array $data)
    {
        try {
            $tender = $this->model->create([
                'customer_id' => $data['customer_id'],
                'tender_id' => $data['tender_id'],
                'tender_number' => $data['tender_number'],
                'tender_title' => $data['tender_title'],
                'tender_description' => $data['tender_description'],
                'closing_date' => $data['closing_date'],
                'closing_time' => $data['closing_time'],
                'tendertype_id' => $data['tendertype_id'],
                'status' => $data['status'],
                'source' => $data['source'],
                'suppliercategories' => $data['suppliercategories'],
            ]);
            if (count($data['tenderfees']) > 0) {
                foreach ($data['tenderfees'] as $tenderfee) {
                    $this->tenderfee->create([
                        'tender_id' => $tender->id,
                        'inventoryitem_id' => $tenderfee['inventoryitem_id'],
                        'currency_id' => $tenderfee['currency_id'],
                        'amount' => $tenderfee['amount'],
                        'validityperiod' => $tenderfee['validityperiod'],
                    ]);
                }
            }

            return ['status' => 'success', 'message' => 'Tender created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function gettenders($search = null)
    {

        return $this->model->with('customer', 'tendertype')->when($search, function ($query) use ($search) {
            $query->where('tender_number', 'like', '%'.$search.'%')->orWhere('tender_title', 'like', '%'.$search.'%');
        })->paginate(100);

    }

    public function gettendersbynumber($tendernumber)
    {
        return $this->model->with('tenderfees.currency', 'tenderfees.inventoryitem')->where('tender_number', 'like', '%'.$tendernumber.'%')->get();
    }

    public function gettender($id)
    {
        return $this->model->with('tenderfees.currency', 'tenderfees.inventoryitem')->where('id', $id)->first();
    }

    public function updatetender($id, array $data)
    {
        try {
            $this->model->where('id', $id)->update([
                'tender_number' => $data['tender_number'],
                'tender_title' => $data['tender_title'],
                'tender_description' => $data['tender_description'],
                'closing_date' => $data['closing_date'],
                'closing_time' => $data['closing_time'],
                'tendertype_id' => $data['tendertype_id'],
                'status' => $data['status'],
                'suppliercategories' => $data['suppliercategories'],
            ]);

            return ['status' => 'success', 'message' => 'Tender updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetender($id)
    {
        try {
            $this->model->where('id', $id)->delete();

            return ['status' => 'success', 'message' => 'Tender deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function gettenderfee($id)
    {
        return $this->tenderfee->where('id', $id)->first();
    }

    public function createtenderfee(array $data)
    {
        try {
            $this->tenderfee->create([
                'tender_id' => $data['tender_id'],
                'inventoryitem_id' => $data['inventoryitem_id'],
                'currency_id' => $data['currency_id'],
                'amount' => $data['amount'],
                'validityperiod' => $data['validityperiod'],
            ]);

            return ['status' => 'success', 'message' => 'Tender fee created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatetenderfee($id, array $data)
    {
        try {
            $this->tenderfee->where('id', $id)->update([
                'tender_id' => $data['tender_id'],
                'inventoryitem_id' => $data['inventoryitem_id'],
                'currency_id' => $data['currency_id'],
                'amount' => $data['amount'],
                'validityperiod' => $data['validityperiod'],
            ]);

            return ['status' => 'success', 'message' => 'Tender fee updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetenderfee($id)
    {
        try {
            $this->tenderfee->where('id', $id)->delete();

            return ['status' => 'success', 'message' => 'Tender fee deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
