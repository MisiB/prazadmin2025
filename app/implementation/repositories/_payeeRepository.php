<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ipayeeInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\services\ipaynowInterface;
use App\Models\Onlinepayment;
use App\Models\Payeeattempt;
use App\Models\Payeedetail;
use App\Notifications\PaymentNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _payeeRepository implements ipayeeInterface
{
    /**
     * Create a new class instance.
     */
    protected $payeedetail;

    protected $payeeattempt;

    protected $suspenserepository;

    protected $bankaccountrepository;

    protected $onlinepayment;

    protected $paynowService;

    public function __construct(Payeedetail $payeedetail, Payeeattempt $payeeattempt, isuspenseInterface $suspenseRepository, ibankaccountInterface $bankaccountrepository, Onlinepayment $onlinepayment, ipaynowInterface $paynowService)
    {
        $this->payeedetail = $payeedetail;
        $this->payeeattempt = $payeeattempt;
        $this->suspenserepository = $suspenseRepository;
        $this->bankaccountrepository = $bankaccountrepository;
        $this->onlinepayment = $onlinepayment;
        $this->paynowService = $paynowService;
    }

    public function getbyemail($email)
    {
        return $this->payeedetail->with('attempts.onlinepayment.currency')->where('email', $email)->first();
    }

    public function getbyuuid($uuid)
    {
        $record = $this->payeeattempt->with('payeedetail', 'onlinepayment.currency', 'onlinepayment.invoice.customer', 'onlinepayment.invoice.inventoryitem')->where('uuid', $uuid)->first();
        if ($record) {
            return [
                'status' => 'success',
                'message' => 'Payee attempt details retrieved successfully',
                'data' => $record,
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Payee attempt details not found',
                'data' => null,
            ];
        }
    }

    public function create(array $details)
    {
        try {

            $check = $this->payeedetail->firstOrCreate(['email' => $details['email']], ['name' => $details['name'], 'surname' => $details['surname'], 'phone' => $details['phone']]);
            $uuid = Str::uuid();
            $poll_url = '';
            $redirect_url = '';
            $onlinepayment = $this->onlinepayment->with('invoice', 'invoice.inventoryitem')->find($details['onlinepayment_id']);
            if (! $onlinepayment) {
                return [
                    'status' => 'error',
                    'message' => 'Online payment not found',
                ];
            }
            if ($onlinepayment->status == 'PAID') {
                return [
                    'status' => 'error',
                    'message' => 'This payment has already been completed',
                ];
            }
            if ($onlinepayment->status == 'CANCELLED') {
                return [
                    'status' => 'error',
                    'message' => 'This payment has been cancelled',
                ];
            }

            if ($details['method'] == 'paynowmerchant') {
                $data = ['email' => $check->email, 'amount' => $onlinepayment->amount, 'description' => $onlinepayment->invoice->inventoryitem->name, 'reference' => $uuid, 'type' => $onlinepayment->invoice->inventoryitem->type, 'currency_id' => $onlinepayment->currency_id];
                $paynowresponse = $this->paynowService->initiatepayment($data);
                if ($paynowresponse['status'] == 'error') {
                    return [
                        'status' => 'error',
                        'message' => $paynowresponse['message'],
                    ];
                } else {
                    $poll_url = $paynowresponse['pollurl'];
                    $redirect_url = $paynowresponse['redirecturl'];
                }
            }

            $check->attempts()->create([
                'onlinepayment_id' => $details['onlinepayment_id'],
                'uuid' => $uuid,
                'method' => $details['method'],
                'status' => 'PENDING',
                'poll_url' => $poll_url,
            ]);

            return [
                'status' => 'success',
                'message' => 'Payee details created successfully',
                'data' => $uuid,
                'redirect_url' => $redirect_url,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(array $details, $uuid)
    {
        try {
            $record = $this->payeeattempt->with('onlinepayment.invoice.inventoryitem')->where('uuid', $uuid)->first();
            if ($record) {
                if ($details['status'] == 'APPROVED') {
                    $bankid = 1;
                    if (isset($details['bank_id'])) {
                        $bankid = $details['bank_id'];
                    }

                    $bankaccount = $this->bankaccountrepository->retrievebankaccount($bankid, $record->onlinepayment->invoice->inventoryitem->type, $record->onlinepayment->currency_id);
                    if (! $bankaccount) {
                        return [
                            'status' => 'error',
                            'message' => 'No bank account found for the specified type and currency',
                        ];
                    }
                    $suspenresponse = $this->suspenserepository->create([
                        'customer_id' => $record->onlinepayment->invoice->customer_id,
                        'sourcetype' => $record->method,
                        'source_id' => $record->id,
                        'amount' => $record->onlinepayment->amount,
                        'currency' => $record->onlinepayment->currency->name,
                        'status' => 'PENDING',
                        'accountnumber' => $bankaccount->account_number,
                        'type' => $record->onlinepayment->invoice->inventoryitem->type,
                        'posted' => 0,
                    ]);
                    if ($suspenresponse['status'] == 'error') {
                        return $suspenresponse;
                    } else {
                        $record->status = 'APPROVED';
                        $record->save();
                        $record->onlinepayment->status = 'PAID';
                        $record->onlinepayment->method = $record->method;
                        $record->onlinepayment->poll_url = $uuid;
                        $record->onlinepayment->save();
                        $message = 'Your payment of ' . $record->onlinepayment->amount . ' ' . $record->onlinepayment->currency->name . ' has been successfully processed.';
                        Notification::route('mail', $record->payeedetail->email)->notify(new PaymentNotification($record->payeedetail->name, $message));

                        return [
                            'status' => 'success',
                            'message' => 'Payee details updated successfully',
                            'data' => $record,
                        ];
                    }
                    // update the online payment as paid

                } else {
                    $record->status = $details['status'];
                    $record->save();
                    $message = 'Your payment of ' . $record->onlinepayment->amount . ' ' . $record->onlinepayment->currency->name . ' failed with status ' . strtolower($details['status']) . '.';
                    Notification::route('mail', $record->payeedetail->email)->notify(new PaymentNotification($record->payeedetail->name, $message));

                    return [
                        'status' => 'error',
                        'message' => 'Payment attempt failed with status: ' . $details['status'],
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Payment attempt not found',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
