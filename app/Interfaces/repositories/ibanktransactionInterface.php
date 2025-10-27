<?php

namespace App\Interfaces\repositories;

interface ibanktransactionInterface
{
    public function getlatesttransactions();
    public function gettransaction($id);
    public function gettransactionbydaterange($startdate, $enddate, $bankaccount=null);
    public function createtransaction(array $data);
    public function recallpayment($refencenumber);
    public function search($needle);
    public function internalsearch($needle);
    public function claim(array $data);
    public function link(array $data);
    public function block($id, $status);
    public function gettransactions($customer_id);

    public function getbankreconciliations($year);
    public function getbankreconciliation($id);
    public function extractdata($id);
    public function syncdata($id);
    public function viewreport($id,$filterbystatus,$showdebit);
    public function createbankreconciliation(array $data);
    public function updatebankreconciliation($id, array $data);
    public function deletebankreconciliation($id);
}
