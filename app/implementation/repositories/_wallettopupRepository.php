<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\repositories\iwallettopupInterface;
use App\Models\Wallettopup;
use Illuminate\Support\Facades\Auth;

class _wallettopupRepository implements iwallettopupInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    protected $suspenserepo;
    public function __construct(Wallettopup $model,isuspenseInterface $suspenserepo)
    {
        $this->model = $model;
        $this->suspenserepo = $suspenserepo;
    }

    public function getwallettopups($year)
    {
        return $this->model->with('customer','currency','initiator','approver')->where('year', $year)->get();
    }

    public function getwallettopup($id)
    {
        return $this->model->with('customer','currency','initiator','approver','banktransaction','suspense.suspenseutilizations','linkeduser')->find($id);
    }

    public function getwallettopupbycustomer($customer_id)
    {
        return $this->model->with('customer','currency','initiator','approver','banktransaction')->where('customer_id', $customer_id)->get();
    }

    public function createwallettopup($data)
    {
        try {
            $this->model->create($data);
            return ["status"=>"success","message"=>"Wallet topup created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>"Failed to create wallet topup: " . $e->getMessage()];
        }
    }

    public function updatewallettopup($id, $data)
    {
        try {
            $this->model->find($id)->update($data);
            return ["status"=>"success","message"=>"Wallet topup updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>"Failed to update wallet topup: " . $e->getMessage()];
        }
    }

    public function deletewallettopup($id)
    {
        try {
            $wallettopup = $this->model->where('id',$id)->first();
            if($wallettopup->status == "PENDING"){
                $wallettopup->delete();
                return ["status"=>"success","message"=>"Wallet topup deleted successfully"];
            }else{
                return ["status"=>"error","message"=>"Wallet topup cannot be deleted"];
            }
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>"Failed to delete wallet topup: " . $e->getMessage()];
        }
    }

    public function makedecision($id,$data)
    {
        try {
            $wallettopup = $this->model->where('id',$id)->first();
            if($wallettopup->status == "PENDING"){
                $wallettopup->status = $data['decision'];
                $wallettopup->approvedby = Auth::user()->id;
                if($data['decision'] == "APPROVED"){
                    $response = $this->suspenserepo->create([
                        "customer_id"=>$wallettopup->customer_id,
                        "sourcetype"=>"Manual",
                        "source_id"=>$wallettopup->id,
                        "amount"=>$wallettopup->amount,
                        "currency"=>$wallettopup->currency->name,
                        "accountnumber"=>$wallettopup->accountnumber,
                        "type"=>$wallettopup->type,
                        "status"=>"PENDING",
                        "method"=>"WALLETTOPUP"
                    ]);
                    if($response['status']=="success"){
                        $wallettopup->suspense_id = $response['data']['id'];
                        $wallettopup->save();
                        return ["status"=>"success","message"=>"Wallet topup decision made successfully"];
                    }else{
                        return ["status"=>"error","message"=>"Failed to create suspense: " . $response['message']];
                    }
                }else{
                    $wallettopup->rejectedreason = $data['rejectedreason'];
                    $wallettopup->save();
                    return ["status"=>"success","message"=>"Wallet topup decision made successfully"];
                }
                
            }else{
                return ["status"=>"error","message"=>"Wallet topup decision cannot be made"];
            }
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>"Failed to make decision: " . $e->getMessage()];
        }
    }
    public function linkwallet($data)
    {
        try {
            $wallettopup = $this->model->where('id',$data['id'])->first();
            if($wallettopup->status == "APPROVED"){
                $wallettopup->banktransaction_id = $data['banktransaction_id'];
                $wallettopup->linkedby = Auth::user()->id;
                $wallettopup->save();
                return ["status"=>"success","message"=>"Wallet topup linked successfully"];
            }else{
                return ["status"=>"error","message"=>"Wallet topup cannot be linked"];
            }
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>"Failed to link wallet: " . $e->getMessage()];
        }
    }
}
