<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ipayeeInterface;
use App\Interfaces\services\ipayeeService;

class _payeeService implements ipayeeService
{
    protected $payeeRepository;

    public function __construct(ipayeeInterface $payeeRepository)
    {
        $this->payeeRepository = $payeeRepository;
    }

    public function getbyemail($email)
    {
        return $this->payeeRepository->getbyemail($email);
    }

    public function getbyuuid($uuid)
    {
        return $this->payeeRepository->getbyuuid($uuid);
    }

    public function create(array $details)
    {
        return $this->payeeRepository->create($details);
    }

    public function update(array $details, $uuid)
    {
        return $this->payeeRepository->update($details, $uuid);
    }

    public function retrievepayments($email)
    {
        return $this->payeeRepository->getbyemail($email);
    }
}
