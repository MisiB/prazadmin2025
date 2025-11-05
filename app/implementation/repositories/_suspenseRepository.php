<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\isuspenseInterface;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Monthlysuspensereport;
use App\Models\Suspense;
use App\Models\Suspenseutilization;
use Illuminate\Support\Facades\DB;

class _suspenseRepository implements isuspenseInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;

    protected $suspenseutilizations;

    protected $monthlysuspense;

    protected $customer;

    protected $currency;

    public function __construct(Suspense $model, Suspenseutilization $suspenseutilizations, Monthlysuspensereport $monthlysuspense, Customer $customer, Currency $currency)
    {
        $this->model = $model;
        $this->suspenseutilizations = $suspenseutilizations;
        $this->monthlysuspense = $monthlysuspense;
        $this->customer = $customer;
        $this->currency = $currency;
    }

    public function create(array $data)
    {
        try {
            $exist = $this->model->where('source_id', $data['source_id'])->where('sourcetype', $data['sourcetype'])->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Transaction already exist'];
            }
            $suspense = $this->model->create([
                'customer_id' => $data['customer_id'],
                'sourcetype' => $data['sourcetype'],
                'source_id' => $data['source_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'accountnumber' => $data['accountnumber'],
                'type' => $data['type'],
                'posted' => 0,
            ]);

            return ['status' => 'success', 'message' => 'Suspense created successfully', 'data' => $suspense];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getpendingsuspensewallets()
    {
        $query = $this->model
            ->select([
                'suspenses.id',
                'suspenses.created_at',
                'suspenses.sourcetype',
                'suspenses.currency',
                'suspenses.accountnumber',
                'suspenses.amount',
                'customers.name as customer_name',
                'customers.regnumber',
                DB::raw('(SELECT created_at FROM suspenseutilizations WHERE suspense_id = suspenses.id ORDER BY created_at DESC LIMIT 1) as last_updated_at'),
                DB::raw('COALESCE(SUM(su.amount), 0) as total_utilized'),
                DB::raw('suspenses.amount - COALESCE(SUM(su.amount), 0) as balance'),
            ])
            ->join('customers', 'suspenses.customer_id', '=', 'customers.id')
            ->leftJoin('suspenseutilizations as su', 'suspenses.id', '=', 'su.suspense_id')
            ->where('suspenses.status', 'PENDING')
            ->where('suspenses.sourcetype', '!=', 'bbf')
            ->groupBy(
                'suspenses.id',
                'suspenses.created_at',
                'suspenses.sourcetype',
                'suspenses.currency',
                'suspenses.accountnumber',
                'suspenses.amount',
                'customers.name',
                'customers.regnumber'
            );

        $results = $query->get();

        $array = [];
        foreach ($results as $row) {
            $array[] = [
                'id' => $row->id,
                'created_at' => $row->created_at,
                'last_updated_at' => $row->last_updated_at,
                'sourcetype' => $row->sourcetype,
                'customer_name' => $row->customer_name,
                'currency' => $row->currency,
                'regnumber' => $row->regnumber,
                'accountnumber' => $row->accountnumber,
                'amount' => $row->amount,
                'total_utilized' => $row->total_utilized,
                'balance' => number_format($row->balance, 2),
            ];
        }

        return $array;
    }

    public function getpendingsuspense($regnumber, $accounttype, $currency)
    {
        return $this->model->with('suspenseutilizations')->where('status', 'PENDING')->where('sourcetype', '!=', 'bbf')->where('type', $accounttype)->where('currency', $currency)->get();
    }

    public function createmonthlysuspensewallets($month, $year)
    {
        $data = $this->model->with('suspenseutilizations', 'customer')->where('status', 'PENDING')->where('sourcetype', '!=', 'bbf')->get();
        if (count($data) > 0) {
            $groupbyaccountnumber = collect($data)->groupBy('accountnumber');
            foreach ($groupbyaccountnumber as $key => $value) {
                $accountnumber = $key;
                $total_amount = $value->sum('amount');
                $currency = $value[0]->currency;
                $total_utilized = $value->map(function ($item) {
                    return $item->suspenseutilizations->sum('amount');
                })->sum();
                $total_balance = $total_amount - $total_utilized;
                $this->monthlysuspense->create([
                    'accountnumber' => $accountnumber,
                    'total_amount' => $total_amount,
                    'total_utilized' => $total_utilized,
                    'total_balance' => $total_balance,
                    'month' => $month,
                    'year' => $year,
                    'currency' => $currency,
                ]);
            }
        }
    }

    public function getmonthlysuspensewallets($month, $year)
    {
        return $this->monthlysuspense
            ->whereRaw('MONTH(created_at) = ? AND YEAR(created_at) = ?', [$month, $year])
            ->get();
    }

    public function getsuspensewallet($regnumber)
    {
        $customer = $this->customer->where('regnumber', $regnumber)->first();
        $wallettypes = ['NONREFUNDABLE', 'REFUNDABLE'];
        $currencylist = $this->currency->where('status', 'ACTIVE')->get();
        $suspnselist = $this->model->with('suspenseutilizations')->where('customer_id', $customer->id)->where('status', 'PENDING')->get();
        $array = [];
        if (count($suspnselist) > 0) {
            foreach ($wallettypes as $wallettype) {
                foreach ($currencylist as $currency) {
                    $suspnse = $suspnselist->where('type', $wallettype)->where('currency', $currency->name)->all();
                    if (count($suspnse) > 0) {
                        $totalsuspense = 0;
                        $totalutilized = 0;
                        foreach ($suspnse as $suspense) {
                            $totalsuspense += $suspense->amount;
                            $totalutilized += $suspense->suspenseutilizations->sum('amount');
                        }
                        $array[] = [
                            'type' => $wallettype,
                            'currency' => $currency->name,
                            'balance' => number_format($totalsuspense - $totalutilized, 2),
                            'regnumber' => $customer->regnumber,
                        ];
                    } else {
                        $array[] = [
                            'type' => $wallettype,
                            'currency' => $currency->name,
                            'balance' => 0,
                            'regnumber' => $customer->regnumber,
                        ];
                    }
                }
            }

            return $array;
        } else {
            foreach ($wallettypes as $wallettype) {
                foreach ($currencylist as $currency) {

                    $array[] = [
                        'type' => $wallettype,
                        'currency' => $currency->name,
                        'balance' => 0,
                        'regnumber' => $customer->regnumber,
                    ];
                }
            }
        }

        return $array;
    }

    public function getsuspensestatement($customer_id)
    {
        $suspenses = $this->model->with('suspenseutilizations.invoice.inventoryitem')->where('customer_id', $customer_id)->get();

        return $suspenses;
    }

    public function getsuspense($id)
    {
        return $this->model->with('suspenseutilizations.invoice.inventoryitem')->where('id', $id)->first();
    }

    public function checksuspsnse($source_id, $sourcetype)
    {
        return $this->model->where('source_id', $source_id)->where('sourcetype', $sourcetype)->first();
    }

    public function getwalletbalance($regnumber, $accounttype, $currency)
    {
        $customer = $this->customer->where('regnumber', $regnumber)->first();
        $suspenses = $this->model->with('suspenseutilizations')->where('customer_id', $customer->id)->where('status', 'PENDING')->where('type', $accounttype)->where('currency', $currency)->get();
        $totalsuspense = 0;
        $totalutilized = 0;
        if (count($suspenses) > 0) {
            foreach ($suspenses as $suspense) {
                $totalsuspense += $suspense->amount;
                $totalutilized += $suspense->suspenseutilizations->sum('amount');
            }
        }

        return number_format($totalsuspense - $totalutilized, 2);
    }

    public function deductwallet($regnumber, $invoice_id, $accounttype, $currency, $amount, $receiptnumber)
    {
        $amount = str_replace(',', '', $amount);

        $amount = ((float) $amount);
        $customer = $this->customer->where('regnumber', $regnumber)->first();
        $suspenses = $this->model->with('suspenseutilizations')->where('customer_id', $customer->id)->where('status', 'PENDING')->where('type', $accounttype)->where('currency', $currency)->get();
        $totalsuspense = 0;
        $totalutilized = 0;
        if (count($suspenses) > 0) {
            foreach ($suspenses as $suspense) {
                $totalsuspense += $suspense->amount;
                $totalutilized += $suspense->suspenseutilizations->sum('amount');
            }
        }
        $walletbalance = number_format($totalsuspense - $totalutilized, 2);
        $finalbalance = (float)str_replace(',', '',$walletbalance);       
        if ($finalbalance < $amount) {
            return ['status' => 'error', 'message' => 'Insufficient balance', 'data' => null];
        }

        foreach ($suspenses as $suspense) {
            $suspensebalance = str_replace(',', '',$suspense->amount) - $suspense->suspenseutilizations->sum('amount');
            // / if suspense balance is less than or equal to amount create  suspenseutilization record and update suspense status to utilized
             //dd($suspensebalance, $amount);
            if ($suspensebalance <= $amount) {
                $this->suspenseutilizations->create([
                    'amount' => $suspensebalance,
                    'suspense_id' => $suspense->id,
                    'invoice_id' => $invoice_id,
                    'receiptnumber' => $receiptnumber,
                ]);
                $suspense->status = 'UTILIZED';
                $suspense->save();
                $amount = $amount - $suspensebalance;
                if ($amount <= 0) {
                    return ['status' => 'success', 'message' => 'Wallet successfully deducted', 'data' => null];
                }
            } else {

                $this->suspenseutilizations->create([
                    'amount' => $amount,
                    'suspense_id' => $suspense->id,
                    'invoice_id' => $invoice_id,
                    'receiptnumber' => $receiptnumber,
                ]);

                $amount = $amount - $suspensebalance;
                if ($amount == 0) {
                    $suspense->status = 'UTILIZED';
                    $suspense->save();
                }

                return ['status' => 'success', 'message' => 'Wallet successfully deducted', 'data' => null];
            }
        }
    }

    public function createSuspenseutilization($suspense_id, $invoice_id, $amount, $receiptnumber)
    {
        try {
            $suspenseutilization = $this->suspenseutilizations->firstOrCreate(['suspense_id' => $suspense_id, 'receiptnumber' => $receiptnumber], [
                'amount' => $amount,
                'suspense_id' => $suspense_id,
                'invoice_id' => $invoice_id,
                'receiptnumber' => $receiptnumber,
            ]);

            return ['status' => 'success', 'message' => 'Suspense utilization created successfully', 'data' => $suspenseutilization];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
