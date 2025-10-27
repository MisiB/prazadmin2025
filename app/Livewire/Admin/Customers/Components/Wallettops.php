<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iwallettopupInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Wallettops extends Component
{
    use WithPagination, Toast;
    public $customer_id;
    protected $repo;
    protected $bankaccountrepo;
    protected $currencyrepo;

    public $id;
    public $amount;
    public $currency_id;
    public $bankaccount_id;
    public $reason;
    public $modal = false;
    public $breadcrumbs = [];
    public $wallettopup = null;
    public $showmodal = false;
    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->breadcrumbs = [
            ["label" => "Customers", "link" => route("admin.customers.showlist")],
            ["label" => "Customer", "link" => route("admin.customers.show", $this->customer_id)],
            ["label" => "Wallet Topups"],
        ];
    }

    public function boot(iwallettopupInterface $repo, ibankaccountInterface $bankaccountrepo, icurrencyInterface $currencyrepo)
    {
        $this->repo = $repo;
        $this->bankaccountrepo = $bankaccountrepo;
        $this->currencyrepo = $currencyrepo;
    }
    public function getWallettopups()
    {
        return $this->repo->getwallettopupbycustomer($this->customer_id);
    }
    public function show($id)
    {
        $this->wallettopup = $this->repo->getwallettopup($id);
        $this->showmodal = true;
    }

    public function save()
    {
        $this->validate([
            "amount" => "required",
            "currency_id" => "required",
            "bankaccount_id" => "required",
            "reason" => "required",
        ]);
        if ($this->id) {
            $this->update();
        } else {
            $this->create();
        }
        $this->reset('amount', 'currency_id', 'bankaccount_id', 'reason');
    }

    public function edit($id)
    {
        $wallettopup = $this->repo->getWallettopup($id);
        $this->id = $wallettopup->id;
        $this->amount = $wallettopup->amount;
        $this->currency_id = $wallettopup->currency_id;
        $this->bankaccount_id = $wallettopup->bankaccount_id;
        $this->reason = $wallettopup->reason;
    }

    public function create()
    {
        $bankaccounnt = $this->getBankaccounts()->where('account_number', $this->bankaccount_id)->first();
        $response = $this->repo->createwallettopup([
            "amount" => $this->amount,
            "currency_id" => $this->currency_id,
            "accountnumber" => $bankaccounnt->account_number,
            "reason" => $this->reason,
            "type" => $bankaccounnt->account_type,
            "customer_id" => $this->customer_id,
            "initiatedby" => Auth::user()->id,
            "year" => date("Y"),
        ]);
        if ($response['status'] == "success") {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function update()
    {
        $bankaccounnt = $this->getBankaccounts()->where('account_number', $this->bankaccount_id)->first();
        $response = $this->repo->updatewallettopup($this->id, [
            "amount" => $this->amount,
            "currency_id" => $this->currency_id,
            "accountnumber" => $bankaccounnt->account_number,
            "reason" => $this->reason,
            "type" => $bankaccounnt->account_type,
            "customer_id" => $this->customer_id,
            "initiatedby" => Auth::user()->id,
            "year" => date("Y"),
        ]);
        if ($response['status'] == "success") {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->repo->deletewallettopup($id);
        if ($response['status'] == "success") {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }



    public function getBankaccounts()
    {
        if($this->currency_id){
            return $this->bankaccountrepo->getBankaccounts()->where('currency_id', $this->currency_id);
        }
        return new \Illuminate\Database\Eloquent\Collection();
    }
    public function getCurrencies()
    {
        return $this->currencyrepo->getcurrencies()->where('status', 'ACTIVE');
    }

    public function headers(): array
    {
        return [
            ["key" => "id", "label" => "ID"],
            ["key" => "amount", "label" => "Amount"],
            ["key" => "accountnumber", "label" => "Bank Account"],
            ["key" => "status", "label" => "Status"],            
            ["key" => "created_at", "label" => "Created At"],
        ];
    }


    public function render()
    {
        return view('livewire.admin.customers.components.wallettops', [
            "headers" => $this->headers(),
            "wallettopups" => $this->getWallettopups(),
            "bankaccounts" => $this->getBankaccounts(),
            "currencies" => $this->getCurrencies(),
        ]);
    }
}
