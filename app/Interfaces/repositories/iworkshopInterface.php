<?php

namespace App\Interfaces\repositories;

interface iworkshopInterface
{
    public function getAllWorkshops();
public function getWorkshopById($id);
    public function getWorkshopWithDetails($id);
    public function createWorkshop(array $data);
    public function updateWorkshop($id, array $data);
    public function deleteWorkshop($id);
    public function getWorkshopOrders($workshop_id);
    public function getOrdersByStatus($workshop_id, $status);
    public function createWorkshopOrder(array $data);
    public function updateWorkshopOrder($order_id, array $data);
    public function deleteWorkshopOrder($order_id);
    public function getWorkshopDelegates($workshop_id);
    public function createDelegate(array $data);
    public function updateDelegate($delegate_id, array $data);
    public function deleteDelegate($delegate_id);
    public function getDelegatesByOrder($order_id);
    public function getOrderSummaries($workshop_id);
    public function exportDelegates($workshop_id);
    
    // Additional methods for supporting data
    public function getCurrencies();
    public function getStatusList();
    public function getTargetList();
    public function getExchangeRates($currency_id);
    public function calculateOrderCost($delegates, $workshop_cost, $exchange_rate);
    public function previewDocument($documentUrl);
    public function searchAccounts($search);
}
