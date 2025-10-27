<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibankInterface;
use App\Interfaces\repositories\icurrencyInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;

class Banks extends Component
{
    use Toast;
    public $name;
    public $email;
    public $status;
    public $bankid;
    public $accountid;
    public $accountnumber;
    public $currencyid;
    public $accounttype;
    public $errormessage="";
    public bool $modal = false;
    public bool $accountmodal = false;
    public bool $addaccountmodal = false;
    public $bankaccounts;
    protected $bankrepo;
    protected $currencyrepo;
    protected $bankaccountrepo;



    public function boot(ibankInterface $repo, icurrencyInterface $currencyrepo, ibankaccountInterface $bankaccountrepo)
    {
        $this->bankrepo = $repo;
        $this->currencyrepo = $currencyrepo;
        $this->bankaccountrepo = $bankaccountrepo;
    }

    public function mount()
    {
        $this->bankaccounts = new Collection();
    }

    public function getbanks()
    {
        return $this->bankrepo->getBanks();
    }

    public function getcurrencies(){
        return $this->currencyrepo->getcurrencies();
    }

    public function getbankaccounts($bank_id)
    {
        $this->bankid = $bank_id;
        $response= $this->bankaccountrepo->getbankaccountsByBank($bank_id);
        $this->bankaccounts = $response;
        $this->accountmodal = true;
    }
    

    public function edit($id)
    {
        $this->bankid = $id;
        $bank = $this->bankrepo->getBank($id);
        if ($bank == null) {
            $this->error("Bank not found");
        }
        $this->name = $bank->name;
        $this->email = $bank->email;
        $this->status = $bank->status;
        $this->modal = true;
    }

    public function save() {
        $this->validate([
            'name' => 'required',
            'email' => 'required',
            'status' => 'required'
        ]);
        if ($this->bankid) {
            $this->update();
        } else {
            $this->create();
        }
        $this->reset(["name", "email", "status","bankid"]);
    }

    public function create()
    {
        $response = $this->bankrepo->createBank(["name" => $this->name, "email" => $this->email, "status" => $this->status, "uuid" => Str::uuid(), "token" => Str::uuid(), "salt" => Str::uuid(),"user_id" => Auth::user()->id]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
        } else {
            $this->errormessage = $response["message"];
        }
    }
    public function update()
    {
        $response = $this->bankrepo->updateBank($this->bankid, ["name" => $this->name, "email" => $this->email, "status" => $this->status]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
        } else {
            $this->errormessage = $response["message"];
        }
    }

    public function delete($id)
    {
        $response = $this->bankrepo->deleteBank($id);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
        } else {
            $this->errormessage = $response["message"];
        }
    }

    public function getaccount($id){
        $this->accountid = $id;
        $account = $this->bankaccountrepo->getBankAccount($id);
        if ($account == null) {
            $this->error("Bank account not found");
        }
        $this->accountnumber = $account->account_number;
        $this->currencyid = $account->currency_id;
        $this->accounttype = $account->account_type;
        $this->status = $account->account_status;
        $this->addaccountmodal = true;
    }

    public function saveaccount() {
        $this->validate([
            'accountnumber' => 'required',
            'currencyid' => 'required',
            'accounttype' => 'required',
            'status' => 'required'
        ]);
        if ($this->accountid) {
            $this->updateaccount();
        } else {
            $this->createaccount();
        }
        $this->reset(["accountnumber", "currencyid", "accounttype", "status", "accountid"]);
    }
    public function createaccount(){
        $response = $this->bankaccountrepo->createBankAccount(["account_number" => $this->accountnumber, "currency_id" => $this->currencyid, "account_type" => $this->accounttype, "account_status" => $this->status, "bank_id" => $this->bankid]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
           $this->getbankaccounts($this->bankid);
        } else {
            $this->errormessage = $response["message"];
        }
    }
    public function updateaccount(){
        $response = $this->bankaccountrepo->updateBankAccount($this->accountid, ["account_number" => $this->accountnumber, "currency_id" => $this->currencyid, "account_type" => $this->accounttype, "account_status" => $this->status]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
            $this->getbankaccounts($this->bankid);
        } else {
            $this->errormessage = $response["message"];
        }
    }
    public function deleteaccount($id){
        $response = $this->bankaccountrepo->deleteBankAccount($id);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
            $this->getbankaccounts($this->bankid);
        } else {
            $this->errormessage = $response["message"];
        }
        
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'status', 'label' => 'Status']

        ];
    }

    public function accountheaders(): array
    {
        return [
            ['key' => 'account_number', 'label' => 'Account Number'],
            ['key' => 'currency.name', 'label' => 'Currency'],
            ['key'=>'account_type', 'label' => 'Account Type'],
            ['key' => 'status', 'label' => 'Status']
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.banks', [
            "banks" => $this->getbanks(),
            "headers" => $this->headers(),
            "accounts"=>$this->bankaccounts,
            "currencies"=>$this->getcurrencies(),
            "accountheaders"=>$this->accountheaders()
        ]);
    }
}
