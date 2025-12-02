<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\isuspenseInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Suspensestatement extends Component
{
    use WithPagination, Toast;
    public $customer_id;
    protected $suspenserepo;
    public $breadcrumbs=[];
    public $showsuspense=null;
    public $showmodal=false;
    public function boot(isuspenseInterface $suspenserepo)
    {
        $this->suspenserepo = $suspenserepo;
    }
    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->showsuspense = null;
        $this->breadcrumbs=[
            ["link" => route("admin.customers.showlist"),"label"=>"Customers"],
            ["link" => route("admin.customers.show", $this->customer_id),"label"=>"Customer"],
            ["label"=>"Suspense Statement"],
        ];
    }

    public function getsuspenselist(): LengthAwarePaginator
    {
        return $this->suspenserepo->getsuspensestatementPaginated($this->customer_id, 15);
    }

    public function getAllSuspenseForSummary(): Collection
    {
        return $this->suspenserepo->getsuspensestatement($this->customer_id);
    }

    public function headers():array{
        return [
            ["key"=>"sourcetype","label"=>"Source Type"],
            ["key"=>"type","label"=>"Account Type"],
            ["key"=>"accountnumber","label"=>"Account Number"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"utilized","label"=>"Utilized"],
            ["key"=>"balance","label"=>"Balance"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"action","label"=>"Action"],
        ];
    }
    public function showSuspense($id){
        $this->showsuspense = $this->suspenserepo->getsuspense($id);
        $this->showmodal=true;
    }

    public function isSuperAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Super Admin');
    }

    public function reverseTransaction($suspenseUtilizationId)
    {
        // Check if user is Super Admin
        if (!$this->isSuperAdmin()) {
            $this->error('Only Super Admin users can reverse transactions');
            return;
        }

        $response = $this->suspenserepo->reverseSuspenseUtilization($suspenseUtilizationId);
        
        if ($response['status'] === 'SUCCESS') {
            $this->success($response['message']);
            // Refresh the suspense details
            if ($this->showsuspense) {
                $this->showsuspense = $this->suspenserepo->getsuspense($this->showsuspense->id);
            }
            // Refresh the suspense list
            $this->dispatch('$refresh');
            // Dispatch event to refresh wallet balances in parent component
            $this->dispatch('wallet-balances-updated');
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.customers.components.suspensestatement',[
            "suspenselist"=>$this->getsuspenselist(),
            "allSuspense"=>$this->getAllSuspenseForSummary(),
            "headers"=>$this->headers(),
        ]);
    }
}
