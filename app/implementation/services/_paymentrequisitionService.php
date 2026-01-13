<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\services\ipaymentrequisitionService;

class _paymentrequisitionService implements ipaymentrequisitionService
{
    protected $repository;

    public function __construct(ipaymentrequisitionInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getpaymentrequisitions($year, $search = null)
    {
        return $this->repository->getpaymentrequisitions($year, $search);
    }

    public function getpaymentrequisition($id)
    {
        return $this->repository->getpaymentrequisition($id);
    }

    public function getpaymentrequisitionbyuuid($uuid)
    {
        return $this->repository->getpaymentrequisitionbyuuid($uuid);
    }

    public function getpaymentrequisitionbydepartment($year, $department_id, $search = null)
    {
        return $this->repository->getpaymentrequisitionbydepartment($year, $department_id, $search);
    }

    public function getpaymentrequisitionsbyapplicant($userId, $year, $search = null)
    {
        return $this->repository->getpaymentrequisitionsbyapplicant($userId, $year, $search);
    }

    public function getpaymentrequisitionbystatus($year, $status)
    {
        return $this->repository->getpaymentrequisitionbystatus($year, $status);
    }

    public function createpaymentrequisition($data)
    {
        return $this->repository->createpaymentrequisition($data);
    }

    public function updatepaymentrequisition($id, $data)
    {
        return $this->repository->updatepaymentrequisition($id, $data);
    }

    public function deletepaymentrequisition($id)
    {
        return $this->repository->deletepaymentrequisition($id);
    }

    public function submitpaymentrequisition($id)
    {
        return $this->repository->submitpaymentrequisition($id);
    }

    public function recommendhod($id, $data)
    {
        return $this->repository->recommendhod($id, $data);
    }

    public function reviewadmin($id, $data)
    {
        return $this->repository->reviewadmin($id, $data);
    }

    public function recommendadmin($id, $data)
    {
        return $this->repository->recommendadmin($id, $data);
    }

    public function approvefinal($id, $data)
    {
        return $this->repository->approvefinal($id, $data);
    }

    public function reject($id, $data)
    {
        return $this->repository->reject($id, $data);
    }
}
