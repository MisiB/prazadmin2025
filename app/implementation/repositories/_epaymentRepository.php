<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iepaymentInterface;
use App\Models\Epayment;
use Illuminate\Support\Str;

class _epaymentRepository implements iepaymentInterface
{
    /**
     * Create a new class instance.
     */
    protected $modal;

    public function __construct(Epayment $modal)
    {
        $this->modal = $modal;
    }

    public function getepayments($customer_id)
    {
        return $this->modal->where('customer_id', $customer_id)->paginate(10);
    }

    public function createepayment($data)
    {
        try {
            $checkpayment = $this->modal->where('invoice_id', $data['invoice_id'])->where('status', 'PAID')->first();
            if ($checkpayment != null) {
                return [
                    'message' => 'A valid payment torwards invoice found',
                    'status' => 'ERROR',
                    'code' => 504,
                    'errors' => ['A valid payment torwards invoice found'],
                    'result' => null,
                ];
            }
            $epayment = $this->modal->create([
                'invoice_id' => $data['invoice_id'],
                'customer_id' => $data['customer_id'],
                'initiation_id' => Str::uuid(),
                'bank_id' => $data['bank_id'],
                'onlinepayment_id' => $data['onlinepayment_id'],
                'currency' => $data['currency'],
                'amount' => $data['amount'],
                'source' => $data['source'],
                'status' => 'PENDING',
            ]);

            return ['status' => 'SUCCESS', 'message' => 'Epayment created successfully', 'data' => $epayment];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    public function getepaymentbyinitiationid($initiationid)
    {
        return $this->modal->with('onlinepayment')->where('initiation_id', $initiationid)->first();
    }

    public function updateepayment($id, $data)
    {
        try {
            $record = $this->modal->with('onlinepayment')->where('id', $id)->first();
            if ($record == null) {
                return ['status' => 'ERROR', 'message' => 'Epayment not found'];
            }

            $record->onlinepayment->status = 'PAID';
            $record->onlinepayment->save();
            $record->reference = $data['Reference'];
            $record->transactiondate = $data['TransactionDate'];
            $record->status = 'PAID';
            $record->save();

            return ['status' => 'SUCCESS', 'message' => 'Epayment updated successfully', 'data' => $record];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }
}
