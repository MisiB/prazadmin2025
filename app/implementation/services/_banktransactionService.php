<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ibankInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use App\Interfaces\services\ibanktransactionInterface as ServicesibanktransactionInterface;

class _banktransactionService implements ServicesibanktransactionInterface
{
    protected $banktransactionrepo;
    protected $bankrepo;
    protected $customerrepo;
    public function __construct(ibanktransactionInterface $banktransactionrepo, ibankInterface $bankrepo, icustomerInterface $customerrepo)
    {
        $this->banktransactionrepo = $banktransactionrepo;
        $this->bankrepo = $bankrepo;
        $this->customerrepo = $customerrepo;
    }

    public function createtransaction($data){   
          return $this->banktransactionrepo->createtransaction($data);        
   }

  
    public function recalltransaction($data){
        return $this->banktransactionrepo->recallpayment($data);
    }
    public function searchtransaction($data){
        return $this->banktransactionrepo->search($data);
    }
    public function claim($data){
        return $this->banktransactionrepo->claim($data);
    }
}
