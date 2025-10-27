<?php

namespace App\Interfaces\repositories;

interface ibankaccountInterface
{
    public function getbankaccounts();

    public function getbankaccount($id);

    public function createbankaccount($data);

    public function updatebankaccount($id, $data);

    public function deletebankaccount($id);

    public function getbankaccountsByBank($bank_id);

    public function retrievebankaccount($bank_id, $type, $currency_id);

    public function getbankaccountbytype($currency_id, $type);

    public function getBankAccountByBankIdAndAccountNumber($bank_id, $account_number);
}
