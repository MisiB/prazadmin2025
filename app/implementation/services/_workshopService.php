<?php

namespace App\implementation\services;

use App\Interfaces\repositories\iworkshopInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Models\Exchangerate;

class _workshopService implements iworkshopInterface
{
    protected $workshopRepo;
    protected $currencyRepo;
    protected $exchangeRateRepo;

    public function __construct(
        iworkshopInterface $workshopRepo,
        icurrencyInterface $currencyRepo,
        iexchangerateInterface $exchangeRateRepo
    ) {
        $this->workshopRepo = $workshopRepo;
        $this->currencyRepo = $currencyRepo;
        $this->exchangeRateRepo = $exchangeRateRepo;
    }

    public function getAllWorkshops()
    {
        return $this->workshopRepo->getAllWorkshops();
    }

    public function getWorkshopById($id)
    {
        return $this->workshopRepo->getWorkshopById($id);
    }

    public function getWorkshopWithDetails($id)
    {
        return $this->workshopRepo->getWorkshopWithDetails($id);
    }

    public function createWorkshop(array $data)
    {
        return $this->workshopRepo->createWorkshop($data);
    }

    public function updateWorkshop($id, array $data)
    {
        return $this->workshopRepo->updateWorkshop($id, $data);
    }

    public function deleteWorkshop($id)
    {
        return $this->workshopRepo->deleteWorkshop($id);
    }

    public function getWorkshopOrders($workshop_id)
    {
        return $this->workshopRepo->getWorkshopOrders($workshop_id);
    }

    public function getOrdersByStatus($workshop_id, $status)
    {
        return $this->workshopRepo->getOrdersByStatus($workshop_id, $status);
    }

    public function createWorkshopOrder(array $data)
    {
        return $this->workshopRepo->createWorkshopOrder($data);
    }

    public function updateWorkshopOrder($order_id, array $data)
    {
        return $this->workshopRepo->updateWorkshopOrder($order_id, $data);
    }

    public function deleteWorkshopOrder($order_id)
    {
        return $this->workshopRepo->deleteWorkshopOrder($order_id);
    }

    public function getWorkshopDelegates($workshop_id)
    {
        return $this->workshopRepo->getWorkshopDelegates($workshop_id);
    }

    public function createDelegate(array $data)
    {
        return $this->workshopRepo->createDelegate($data);
    }

        public function updateDelegate($delegate_id, array $data)
    {
        return $this->workshopRepo->updateDelegate($delegate_id, $data);
    }

    public function deleteDelegate($delegate_id)
    {
        return $this->workshopRepo->deleteDelegate($delegate_id);
    }

    public function getDelegatesByOrder($order_id)
    {
        return $this->workshopRepo->getDelegatesByOrder($order_id);
    }

    public function getOrderSummaries($workshop_id)
    {
        return $this->workshopRepo->getOrderSummaries($workshop_id);
    }

    public function exportDelegates($workshop_id)
    {
        return $this->workshopRepo->exportDelegates($workshop_id);
    }

    public function getCurrencies()
    {
        return $this->currencyRepo->getcurrencies()->filter(function ($currency) {
            return in_array(strtoupper($currency->status), ['ACTIVE']);
        })->values();
    }

    public function getStatusList()
    {
        return [
            ['id' => 'PENDING', 'name' => 'Pending'],
            ['id' => 'PUBLISHED', 'name' => 'Published'],
            ['id' => 'CANCELLED', 'name' => 'Cancelled'],
        ];
    }

    public function getTargetList()
    {
        return [
            ['id' => 'PE', 'name' => 'Procurement entities'],
            ['id' => 'BIDDER', 'name' => 'Bidders'],
            ['id' => 'ALL', 'name' => 'ALL'],
        ];
    }



    public function getExchangeRates($currency_id)
{
    if (!$currency_id) {
        return collect();
    }

    $rates = Exchangerate::where('secondary_currency_id', $currency_id)->get();
    return $rates->map(function ($rate) {
        return [
            'id' => $rate->id,
            'type' => $rate->type,
            'primary_currency_id' => $rate->primary_currency_id,
            'secondary_currency_id' => $rate->secondary_currency_id,
            'value' => $rate->value,
            'created_at' => $rate->created_at,
        ];
    });
}

    public function calculateOrderCost($delegates, $workshop_cost, $exchange_rate)
    {
        return ($delegates * $workshop_cost) * $exchange_rate;
    }

    public function previewDocument($documentUrl)
    {
        return asset('storage/' . $documentUrl);    
    }

    public function searchAccounts($search)
    {
        return $this->workshopRepo->searchAccounts($search);
    }
}
