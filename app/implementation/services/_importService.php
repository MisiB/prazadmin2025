<?php

namespace App\implementation\services;


use App\Interfaces\repositories\icustomerInterface;
use App\Models\Bankaccount;
use App\Models\Banktransaction;
use App\Models\Currency;
use App\Models\selfservicedb\Account;
use App\Models\selfservicedb\SlfBanktransaction;
use App\Interfaces\services\IImportService;
class _importService implements IImportService
{

    /**
     * Create a new class instance.
     */
    protected $customerrepo;
    protected $selfservicedb_account;
    protected $selfservicedb_banktransaction;
    protected $banktransactionmodel;
    protected $bankaccountmodel;
    protected $currencymodel;
    public function __construct(icustomerInterface $customerrepo,Account $selfservicedb_account,SlfBanktransaction $selfservicedb_banktransaction,Banktransaction $banktransactionmodel,Bankaccount $bankaccountmodel,Currency $currencymodel)
    {
        $this->customerrepo = $customerrepo;
        $this->selfservicedb_account = $selfservicedb_account;
        $this->selfservicedb_banktransaction = $selfservicedb_banktransaction;
        $this->banktransactionmodel = $banktransactionmodel;
        $this->bankaccountmodel = $bankaccountmodel;
        $this->currencymodel = $currencymodel;
    }
    public function importcustomers(){
        $accounts = $this->selfservicedb_account->all();
        $i = 0;
        foreach ($accounts as $account) {
           $response = $this->customerrepo->create([
               "id"=>$account->id,
               "name"=>$account->Name,
               "type"=>$account->Type,
               "regnumber"=>$account->Regnumber,
               "country"=>$account->country??"ZIMBABWE",
               'created_at'=>$account->created_at,
               'updated_at'=>$account->updated_at
            ]);
            if($response['status']=='error'){
                echo $response['message']."\n";
            }
            $i++;
            echo $i."created\n";
        }
        return $i;
    }
    public function importbanktransactions(){
        $banktransactions = $this->selfservicedb_banktransaction->all();
        $i = 0;
        foreach ($banktransactions as $banktransaction) {
            $bankaccount = $this->bankaccountmodel->where("account_number",$banktransaction->Accountnumber)->first();
            if(!$bankaccount){
                echo $banktransaction->Accountnumber."Bank account not found with id".$banktransaction->id."\n";
                continue;
            }
            $currency = $this->currencymodel->where("name",$banktransaction->Currency)->first();
            if(!$currency){
                echo $banktransaction->Currency."Currency not found with id".$banktransaction->id."\n";
                continue;
            }
            try{
            
            $this->banktransactionmodel->firstOrCreate(["sourcereference"=>$banktransaction->SourceReference],[
                "id"=>$banktransaction->id,
                "bank_id"=>$banktransaction->BankId,
                 "currency_id"=>$currency->id,
                 "customer_id"=>$banktransaction->AccountId,
                 "bankaccount_id"=>$bankaccount->id,
                 "transactiondate"=>$banktransaction->TransactionDate,
                 "referencenumber"=>$banktransaction->Referencenumber,
                 "statementreference"=>$banktransaction->StatementReference,
                 "sourcereference"=>$banktransaction->SourceReference,
                 "description"=>$banktransaction->Description,
                 "accountnumber"=>$banktransaction->Accountnumber,
                 "amount"=>$banktransaction->Amount,
                 "currency"=>$currency->name,
                 "regnumber"=>$banktransaction->Regnumber,
                 "status"=>$banktransaction->Status,
                 "copied"=>$banktransaction->Copied,
                 "user_id"=>$banktransaction->user_id,
                 "created_at"=>$banktransaction->created_at,
                 "updated_at"=>$banktransaction->updated_at                 
            ]);
       
            $i++;
           // echo $i."created \n";
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
            continue;
        }
        }
        return $i;
    }
}
