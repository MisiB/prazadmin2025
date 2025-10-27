<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ipaynowintegrationsInterface;
use App\Interfaces\services\ipaynowInterface;
use Paynow\Payments\Paynow;

class _paynowService implements ipaynowInterface
{
    /**
     * Create a new class instance.
     */
    protected $paynowintegrationsrepo;

    public function __construct(ipaynowintegrationsInterface $paynowintegrationsrepo)
    {
        $this->paynowintegrationsrepo = $paynowintegrationsrepo;
    }

    public function initiatepayment($data)
    {

        $paynowintegrations = $this->paynowintegrationsrepo->getpaynowparameters(['type' => $data['type'], 'currency_id' => $data['currency_id']]);
        if ($paynowintegrations == null) {
            return ['status' => 'error', 'message' => 'Paynow integration not found'];
        }
        $mode = config('paynowconfig.mode');
        $token = $paynowintegrations->token;
        $key = $paynowintegrations->key;
        $email = $mode == 'test' ? 'benson.misi@outlook.com' : $data['email'];
        $paynow = new Paynow($key, $token, config('paynowconfig.return_url').$data['reference'], config('paynowconfig.return_url').$data['reference']);
        $payment = $paynow->createPayment($data['reference'], $email);
        $payment->add($data['description'], $data['amount']);
        $response = $paynow->send($payment);
        if ($response->success()) {
            return ['status' => 'success', 'message' => 'Payment initiated successfully', 'pollurl' => $response->pollUrl(), 'redirecturl' => $response->redirectUrl()];
        } else {
            return ['status' => 'error', 'message' => 'Payment initiation failed'];
        }
    }

    public function checkpaymentstatus($data)
    {
        $paynowintegrations = $this->paynowintegrationsrepo->getpaynowparameters(['type' => $data['type'], 'currency_id' => $data['currency_id']]);
        if ($paynowintegrations == null) {
            return ['status' => 'error', 'message' => 'Paynow integration not found'];
        }
        $paynow = new Paynow($paynowintegrations->key, $paynowintegrations->token, $data['returnurl'], $data['returnurl']);
        $status = $paynow->pollTransaction($data['pollurl']);
        if ($status->paid()) {
            return ['status' => 'PAID'];
        }

        return ['status' => $status->status()];
    }
}
