<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ionlinepaymentInterface;
use App\Interfaces\services\ionlinepaymentService;

class _onlinepaymentService implements ionlinepaymentService
{
    /**
     * Create a new class instance.
     */
    protected $repo;

    public function __construct(ionlinepaymentInterface $repo)
    {
        $this->repo = $repo;
    }

    public function initiatepayment($data)
    {
        return $this->repo->initiatepayment($data);
    }

    public function checkpaymentstatus($uuid)
    {
        return $this->repo->checkpaymentstatus($uuid);
    }

    public function getepayment($uuid)
    {
        return $this->repo->getpaymentbyuuid($uuid);
    }
}
