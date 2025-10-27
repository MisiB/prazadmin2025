<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibankInterface;
use App\Interfaces\repositories\ipayeeInterface;
use App\Interfaces\services\iepaymentService;

class _epaymentService implements iepaymentService
{
    /**
     * Create a new class instance.
     */
    protected $bankrepo;

    protected $bankaccountrepo;

    protected $payeeRepository;

    public function __construct(ibankInterface $bankrepo, ibankaccountInterface $bankaccountrepo, ipayeeInterface $payeeRepository)
    {
        $this->bankrepo = $bankrepo;
        $this->bankaccountrepo = $bankaccountrepo;
        $this->payeeRepository = $payeeRepository;
    }

    public function checkinvoice($data)
    {
        $bank = $this->bankrepo->getBankByToken($data['token']);
        if (! $bank) {
            return [
                'message' => 'Unauthorized',
                'status' => 'ERROR',
                'code' => 401,
                'errors' => ['Unauthorized'],
                'result' => null,
            ];
        }

        /*   $onlinepayment = $this->onlinepaymentrepo->getpaymentbyuuid($data['invoicenumber']);
        if (!$onlinepayment) {
            return [
                'message' => 'Online payment transaction not found',
                'status' => 'ERROR',
                'code' => 404,
                'errors' => null,
                'result' => null,
            ];
        }
        $invoice = $this->invoicerepo->getInvoiceByInvoiceNumber($onlinepayment->invoicenumber);
        if (!$invoice) {
            return [
                'message' => 'Invoice not found',
                'status' => 'ERROR',
                'code' => 404,
                'errors' => ['Invoice not found'],
                'result' => null,
            ];
        }
        if ($invoice->status == 'PAID') {
            return [
                'message' => 'Invoice already settled',
                'status' => 'ERROR',
                'code' => 504,
                'errors' => ['Invoice already settled'],
                'result' => null,
            ];
        }

        $bankaccount = $this->bankaccountrepo->retrievebankaccount($bank->id, $invoice->inventoryitem->type, $invoice->currency_id);
        if (!$bankaccount) {
            return [
                'message' => 'Bank account not found',
                'status' => 'ERROR',
                'code' => 404,
                'errors' => ['Bank account not found'],
                'result' => null,
            ];
        }
        $amount = $onlinepayment->amount;
        $epaymentresponse = $this->epaymentrepo->createepayment([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'bank_id' => $bank->id,
            'onlinepayment_id' => $onlinepayment->id,
            'currency' => $invoice->currency->name,
            'amount' => $amount,
            'source' => 'egp',
            'status' => 'PENDING',
        ]);*/
        $epaymentresponse = $this->payeeRepository->getbyuuid($data['invoicenumber']);
        if (strtoupper($epaymentresponse['status']) == 'SUCCESS') {
            $epayment = $epaymentresponse['data'];

            $bankaccount = $this->bankaccountrepo->retrievebankaccount($bank->id, $epayment->onlinepayment->invoice->inventoryitem->type, $epayment->onlinepayment->invoice->currency_id);
            if (! $bankaccount) {
                return [
                    'message' => 'Bank account not found',
                    'status' => 'ERROR',
                    'code' => 404,
                    'errors' => ['Bank account not found'],
                    'result' => null,
                ];
            }
            $initiationresponse = [
                'status' => $epayment->Status,
                'initiationId' => $epayment->uuid,
                'invoicenumber' => $epayment->onlinepayment->invoice->invoicenumber,
                'accountnumber' => $bankaccount->account_number,
                'service' => $epayment->onlinepayment->invoice->inventoryitem->name,
                'purpose' => $epayment->onlinepayment->invoice->description,
                'Currency' => $epayment->onlinepayment->invoice->currency->name,
                'amount' => round($epayment->onlinepayment->amount, 2),
                'prnumber' => $epayment->onlinepayment->invoice->customer->regnumber,
                'accountname' => $epayment->onlinepayment->invoice->customer->name,
            ];

            return [
                'message' => 'Transaction successfully initiated',
                'status' => 'SUCCESS',
                'code' => 200,
                'errors' => null,
                'result' => collect($initiationresponse),
            ];
        } else {
            return $epaymentresponse;
        }
    }

    public function posttransaction($data)
    {
        $epayment = $this->payeeRepository->getbyuuid($data['initiationId']);
        if ($epayment == null) {
            return [
                'message' => 'Transaction not found with ID: '.$data['initiationId'],
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        if ($epayment->status == 'PAID') {
            return [
                'message' => 'transaction already settled',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => ['redirecturl' => $epayment->onlinepayment->return_url],
            ];
        }

        $invoice = $epayment->onlinepayment->invoice;
        if ($invoice == null) {
            return [
                'message' => 'Invoice not found',
                'status' => 'ERROR',
                'code' => 404,
                'errors' => ['Invoice not found'],
                'result' => null,
            ];
        }
        if ($invoice->status == 'PAID') {
            return [
                'message' => 'Invoice already settled',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        if ($invoice->currency->name != $data['Currency']) {
            return [
                'message' => 'Currency provided is different from invoiced currency'.$invoice->currency->name.' and '.$data['Currency'],
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        if ($epayment->amount != $data['Amount']) {
            return [
                'message' => 'Amount provided is different from invoiced amount',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        $response = $this->payeeRepository->update($epayment->uuid, ['Status' => 'PAID']);
        if ($response['status'] == 'SUCCESS') {
            return [
                'message' => 'Transaction successfully settled',
                'status' => 'SUCCESS',
                'code' => 200,
                'errors' => null,
                'result' => null,
            ];
        } else {
            return [
                'message' => 'Transaction failed to settle',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        /*

        $bankaccount = $this->bankaccountrepo->retrievebankaccount($epayment->bank_id, $invoice->inventoryitem->type, $invoice->currency_id);
        if ($bankaccount == null) {
            return [
                'message' => 'Bank account not found',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }
        $checksuspense = $this->suspenserepo->checksuspsnse($epayment->id, 'epayment');
        if ($checksuspense != null) {
            return [
                'message' => 'Transaction already saved',
                'status' => 'ERROR',
                'code' => 500,
                'errors' => null,
                'result' => null,
            ];
        }

            $epaymentresponse = $this->epaymentrepo->updateepayment($epayment->id, $data);

            if ($epaymentresponse['status'] == 'SUCCESS') {
                $epayment = $epaymentresponse['data'];

                $suspenseresponse = $this->suspenserepo->create([
                    'customer_id' => $invoice->customer_id,
                    'sourcetype' => 'epayment',
                    'source_id' => $epayment->id,
                    'amount' => $epayment->amount,
                    'currency' => $epayment->currency,
                    'status' => 'PENDING',
                    'accountnumber' => $bankaccount->account_number,
                    'type' => $bankaccount->account_type,
                    'posted' => 0,
                ]);
                if (strtoupper($suspenseresponse['status']) == 'SUCCESS') {
                    $epayment->status = 'PAID';
                    $epayment->save();
                    return [
                        'message' => 'Transaction successfully settled',
                        'status' => 'SUCCESS',
                        'code' => 200,
                        'errors' => null,
                        'result' => null,
                    ];
                }
            }*/
    }
}
