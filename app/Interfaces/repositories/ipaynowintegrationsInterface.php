<?php

namespace App\Interfaces\repositories;

interface ipaynowintegrationsInterface
{
    public function getpaynowintegrations();
    public function getpaynowintegration($id);
    public function getpaynowparameters($data);
    public function createpaynowintegration($data);
    public function updatepaynowintegration($id, $data);
    public function deletepaynowintegration($id);
}
 