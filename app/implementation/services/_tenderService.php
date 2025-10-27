<?php

namespace App\implementation\services;

use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\itenderInterface;
use App\Interfaces\services\itenderService;

class _tenderService implements itenderService
{
    /**
     * Create a new class instance.
     */
    protected $repo;

    protected $customerrepo;

    public function __construct(itenderInterface $repo, icustomerInterface $customerrepo)
    {
        $this->repo = $repo;
        $this->customerrepo = $customerrepo;
    }

    public function createtender($data)
    {
        $customer = $this->customerrepo->getCustomerByRegnumber($data['regnumber']);
        if (! $customer) {
            return ['status' => 'ERROR', 'message' => 'Account does not exist'];
        }
        $tender = $this->repo->gettendersbynumber($data['tender_number']);
        if ($tender) {
            return ['status' => 'ERROR', 'message' => 'Tender already exists'];
        }
        $tendertype = $this->repo->gettendertype($data['tendertype']);
        $payload['customer_id'] = $customer->id;
        $payload['tendertype_id'] = $tendertype->id;
        $payload['tender_number'] = $data['tender_number'];
        $payload['source'] = 'EGP';
        $payload['tender_title'] = $data['tender_title'];
        $payload['tender_description'] = $data['tender_description'];
        $payload['closing_date'] = $data['closing_date'];
        $payload['closing_time'] = $data['closing_time'];
        $payload['suppliercategories'] = $data['suppliercategories'];
        $payload['tenderfees'] = $data['tenderfees'];

        return $this->repo->create($payload);
    }

    public function updatetender($id, $data)
    {
        return $this->repo->updatetender($id, $data);
    }

    public function changestatus($id, $status)
    {
        return $this->repo->changestatus($id, $status);
    }
}
