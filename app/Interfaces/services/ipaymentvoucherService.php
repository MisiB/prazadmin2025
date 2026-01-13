<?php

namespace App\Interfaces\services;

interface ipaymentvoucherService
{
    public function getvouchers($year, $search = null);

    public function getvoucher($id);

    public function getvoucherbyuuid($uuid);

    public function getvoucherbystatus($year, $status);

    public function createvoucher($data);

    public function updatevoucher($id, $data);

    public function deletevoucher($id);

    public function submitvoucher($id);

    public function geteligibleitems($year);

    public function verify($id, $data);

    public function check($id, $data);

    public function approverfinance($id, $data);

    public function approveceo($id, $data);

    public function reject($id, $data);

    public function approve($id, $data);

    public function getallvouchers($year);

    public function getconfig($key);

    public function setconfig($key, $value, $description = null);

    public function getallconfigs();
}
