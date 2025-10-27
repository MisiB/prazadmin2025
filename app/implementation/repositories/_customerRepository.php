<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\icustomerInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\Date;

class _customerRepository implements icustomerInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Customer $model)
    {
        $this->model = $model;
    }
    public function getall()
    {
        return $this->model->get();
    }
    public function getCustomerByRegnumber($regnumber)
    {
        return $this->model->where("regnumber",$regnumber)->first();
    }

    public function getCustomerById($id)
    {
        return $this->model->with(['onlinepayments','tenders','banktransactions','epayments','suspenses'])->find($id);
    }
    public function retrieve_last_regnumber($type){
      
        $number = Date::now()->format('Y').rand(1000,99999);
         return $type=="BIDDER" ? "PR".$number : "PE".$number;
    }
    public function search($needle)
    {
        return $this->model->where("regnumber","like","%".$needle."%")->orWhere("name","like","%".$needle."%")->get();
    }
    public function create(array $data)
    {
        try{
           $check = $this->model->where("regnumber",$data["regnumber"])->first();
          if($check){
             return ['status'=>'error','message'=>'Customer already exists'];
            }
            $this->model->create($data);
            return ['status'=>'success','message'=>'Customer created successfully'];
        }catch(\Exception $e){
            return ['status'=>'error','message'=>$e->getMessage()];
        }
    }
    public function update(array $data, $id)
    {
        try{
            $this->model->find($id)->update($data);
            return ['status'=>'success','message'=>'Customer updated successfully'];
        }catch(\Exception $e){
            return ['status'=>'error','message'=>$e->getMessage()];
        }
    }
    public function delete($id)
    {
        try{
           $customer= $this->model->with(['onlinepayments','tenders','banktransactions','epayments','suspenses'])->where("id",$id)->first();
            if($customer == null){
                return ['status'=>'error','message'=>'Customer not found'];
            }
            if($customer->onlinepayments->count() > 0 || $customer->tenders->count() > 0 || $customer->banktransactions->count() > 0 || $customer->epayments->count() > 0 || $customer->suspenses->count() > 0 || $customer->users->count() > 0){
                return ['status'=>'error','message'=>'Customer has related records cannot be deleted'];
            }           
            $customer->delete();
            return ['status'=>'success','message'=>'Customer deleted successfully'];
        }catch(\Exception $e){
            return ['status'=>'error','message'=>$e->getMessage()];
        }
    }
    public function searchname($name,$type){
        $accounts = strtoupper($type) =="ENTITY" ? $this->model->where("Type","=","ENTITY")->get() : $this->model->where("Type","!=","ENTITY")->get();
   
    foreach($accounts as $account){
        $changename = $this->normalizename($account->name);

        if($changename == $this->normalizename($name)){
            return $account;
        }
    }
    return null;
    }
   
    public function normalizename($name){
        $changename =  preg_replace('/[^0-9a-zA-Z\._]/', '', strtoupper($name));
  $changename  = str_replace("PRIVATELIMITED","",$changename);
  $changename  = str_replace("PVTLTD","",$changename);
  $changename  = str_replace("PBC","",$changename);
  return $changename;  
    }
}