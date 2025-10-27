<?php

namespace App\implementation\services;

use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\services\isuspenseService;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\invoiceInterface;

class _suspenseService implements isuspenseService
{
    /**
     * Create a new class instance.
     */
    protected $repository;
    protected $customerrepo;
    protected $invoicerepo; 
    public function __construct(isuspenseInterface $repository,icustomerInterface $customerrepo,invoiceInterface $invoicerepo)
    {
        $this->repository = $repository;
        $this->customerrepo = $customerrepo;
        $this->invoicerepo = $invoicerepo;
    }
    public function getpendingsuspensewallets(){
        return $this->repository->getpendingsuspensewallets();
    }
    public function create(array $data){
        return $this->repository->create($data);
    }
    public function createmonthlysuspensewallets($month,$year){
        return $this->repository->createmonthlysuspensewallets($month,$year);
    }
    public function getmonthlysuspensewallets($month,$year){
        return $this->repository->getmonthlysuspensewallets($month,$year);
    }
    public function getsuspensewallet($regnumber){
        return $this->repository->getsuspensewallet($regnumber);
    }
    public function getsuspense($id){
        return $this->repository->getsuspense($id);
    }
    public function getsuspensestatement($customer_id){
        return $this->repository->getsuspensestatement($customer_id);
    }
    public function getwalletbalance($regnumber,$accounttype,$currency){
        return $this->repository->getwalletbalance($regnumber,$accounttype,$currency);
    }
    public function deductwallet($regnumber,$invoice_id,$accounttype,$currency,$amount,$receiptnumber){
        return $this->repository->deductwallet($regnumber,$invoice_id,$accounttype,$currency,$amount,$receiptnumber);
    }
    public function suspenseutilization($data){
    
        //get customer by regnumber
        $customer = $this->customerrepo->getCustomerByRegnumber($data['regnumber']);
        
        if(!$customer){
            return ['status'=>'ERROR','message'=>'Account not found'];
        }
        //get invoice by invoice number
        $invoice = $this->invoicerepo->getInvoiceByInvoiceNumber($data['invoice_number']);
        if(!$invoice){
            return ['status'=>'ERROR','message'=>'Invoice not found'];
        }
        //get wallet balance
        $walletbalance = $this->repository->getwalletbalance($customer->regnumber,$data['accounttype'],$invoice->currency->name);
        if((double)$invoice->amount>(double)$walletbalance){
            return ['status'=>'ERROR','message'=>"Insufficient funds in wallet of type ".$data['accounttype']." using currency ".$invoice->currency->name];
        }
        //get pendingsuspense
        $pendingsuspense = $this->repository->getpendingsuspense($customer->regnumber,$data['accounttype'],$invoice->currency->name);
        if(count($pendingsuspense)>0){
            return ['status'=>'ERROR','message'=>"Insufficient funds in wallet of type ".$data['accounttype']." using currency ".$invoice->currency->name];
        }
        /// check  invoice balance
        $invoicebalance = $invoice->amount-$invoice->receipts->sum('amount');
        if((double)$invoicebalance<=0){
            $response = $this->invoicerepo->markInvoiceAsPaid($invoice->invoicenumber);
            if($response['status']=='ERROR'){
                return $response;
            }
            return ['status' => 'SUCCESS', 'message' => 'Invoice successfully settled'];
        }
        //create suspenseutilization
        foreach($pendingsuspense as $suspense){

      $availableBalance = round(round($suspense->amount,2)- round($suspense->suspenseutilizations->sum('amount'), 2),2);
         if((double)$availableBalance<=0){
            $suspense->status = "UTILIZED";
            $suspense->save();
         }else{
            $amountToUtilize = min($invoicebalance, $availableBalance);
            $balanceDue = $balanceDue - $amountToUtilize; // Deduct the utilized amount from balance due
            $response = $this->repository->createSuspenseutilization($suspense->id,$invoice->id,$amountToUtilize,$data['receiptnumber']);
            if($response['status']=='ERROR'){
                return $response;
            }
            $invoice = $this->invoicerepo->getInvoiceDetails($invoice->id);
            $invoicebalance = $invoice->amount-$invoice->receipts->sum('amount');
            if($invoicebalance<=0){
                $response = $this->invoicerepo->markInvoiceAsPaid($invoice->invoicenumber);
                if($response['status']=='SUCCESS'){                    
                return ['status'=>'SUCCESS','message'=>'Invoice successfully settled'];
                }
            }
         }
        }
       
       
       
        
       

    }
}
