<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Models\Paymentrequisition;
use App\Models\Paymentrequisitiondocument;
use App\Models\Purchaserequisitionaward;
use Illuminate\Support\Facades\Auth;

class _paymentrequisitionRepository implements ipaymentrequisitionInterface
{
    /**
     * Create a new class instance.
     */
    protected $paymentrequisition;
    protected $purchaserequisitionaward;
    protected $paymentrequisitiondocument;
    public function __construct(Paymentrequisition $paymentrequisition,Purchaserequisitionaward $purchaserequisitionaward,Paymentrequisitiondocument $paymentrequisitiondocument)
    {
        $this->paymentrequisition = $paymentrequisition;
        $this->purchaserequisitionaward = $purchaserequisitionaward;
        $this->paymentrequisitiondocument = $paymentrequisitiondocument;
    }
    public function getpaymentrequisitions($year){

        return $this->paymentrequisition->where('year', $year)->get();

    }
    public function getpaymentrequisitionbydepartment($year,$department_id){
        return $this->paymentrequisition->with('approvals','documents','department','createdby')->where('year', $year)->where('department_id', $department_id)->get();

    }
    public function getpaymentrequisition($id){

        return $this->paymentrequisition->with('approvals','documents','department','createdby')->where('id', $id)->first();

    }
    public function createpaymentrequisition($data){
        try{
            if($data["source"]=="purchaserequisitionaward"){
                $purchaserequisitionaward = $this->purchaserequisitionaward->with('paymentrequisitions','purchaserequisition')->where('id', $data['source_id'])->first();
                $totalawardvalue = $purchaserequisitionaward->amount;
                $totalpaymentrequisitionvalue = $purchaserequisitionaward?->paymentrequisitions?->sum('amount')? $purchaserequisitionaward?->paymentrequisitions?->sum('amount') :0;
                $remainingvalue = $totalawardvalue - $totalpaymentrequisitionvalue;
                if($remainingvalue<0){
                    return ["status"=>"error","message"=>"Remaining value is less than 0"];
                }
                if($remainingvalue<$data['amount']){
                    return ["status"=>"error","message"=>"Payment request amount cannot be greater than ".$remainingvalue];
                }
                $data['year'] = $purchaserequisitionaward->year;
                $data['department_id'] = Auth::user()->department->department_id;
                $data['source_id'] = $purchaserequisitionaward->id;
                $data['source'] = "purchaserequisitionaward";
                $data['title'] = $data['title'];
                $data['description'] = $data['description'];
                $data['quantity'] = $data['quantity'];
                $data['amount'] = $data['amount'];
                $data['status'] = "PENDING";
                $data['created_by'] = Auth::user()->id;
            }
            unset($data['recommend']);
            $paymentrequisition = $this->paymentrequisition->create($data);
            return ["status"=>"success","message"=>"Payment Requisition Created Successfully","data"=>$paymentrequisition];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }

    }
    public function updatepaymentrequisition($id, $data){
        try{
            $paymentrequisition = $this->paymentrequisition->with('documents')->find($id);
            if(!$paymentrequisition){
                return ["status"=>"error","message"=>"Payment Requisition Not Found"];
            }
            if($data['recommend']){
                if($paymentrequisition->documents->count() > 0){
                    $data['status'] = $data['recommend'] ? "AWAITING_RECOMMENDATION" : "PENDING";
                }else{
                    return ["status"=>"error","message"=>"Please upload supporting documents"];
                }
            }
          
            unset($data['recommend']);
            

            $paymentrequisition->update($data);
            return ["status"=>"success","message"=>"Payment Requisition Updated Successfully","data"=>$paymentrequisition];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletepaymentrequisition($id){
        try{
            $paymentrequisition = $this->paymentrequisition->find($id);
            if(!$paymentrequisition){
                return ["status"=>"error","message"=>"Payment Requisition Not Found"];
            }
            if($paymentrequisition->status!="PENDING"){
                return ["status"=>"error","message"=>"Payment Requisition cannot be deleted as it is not in PENDING status"];
            }
            $paymentrequisition->delete();
            return ["status"=>"success","message"=>"Payment Requisition Deleted Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function paymentrequisitionrecommend($id){
        try{
            $paymentrequisition = $this->paymentrequisition->with('purchaserequisitionaward.purchaserequisition')->find($id);
            if(!$paymentrequisition){
                return ["status"=>"error","message"=>"Payment Requisition Not Found"];
            }
            if($paymentrequisition->status=="APPROVED"){
                return ["status"=>"error","message"=>"Payment Requisition Already Approved"];
            }
            $paymentrequisition->update(["status"=>"APPROVED"]);

           if($paymentrequisition->quantity == $paymentrequisition->purchaserequisitionaward->purchaserequisition->quantity){
            $paymentrequisition->purchaserequisitionaward->purchaserequisition->update(["status"=>"DELIVERED"]);
           }
            return ["status"=>"success","message"=>"Payment Requisition Recommended Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getpaymentrequisitiondocuments($id){
        return $this->paymentrequisitiondocument->where('paymentrequisition_id', $id)->get();
    }
    public function createdocument($data){
        try{
            $paymentrequisitiondocument = $this->paymentrequisitiondocument->create($data);
            return ["status"=>"success","message"=>"Payment Requisition Document Created Successfully","data"=>$paymentrequisitiondocument];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updatedocument($id, $data){
        try{
            $paymentrequisitiondocument = $this->paymentrequisitiondocument->find($id);
            if(!$paymentrequisitiondocument){
                return ["status"=>"error","message"=>"Payment Requisition Document Not Found"];
            }
            $paymentrequisitiondocument->update($data);
            return ["status"=>"success","message"=>"Payment Requisition Document Updated Successfully","data"=>$paymentrequisitiondocument];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletedocument($id){
        try{
            $paymentrequisitiondocument = $this->paymentrequisitiondocument->find($id);
            if(!$paymentrequisitiondocument){
                return ["status"=>"error","message"=>"Payment Requisition Document Not Found"];
            }
            $paymentrequisitiondocument->delete();
            return ["status"=>"success","message"=>"Payment Requisition Document Deleted Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getpaymentrequisitionbysource($source,$source_id){
      return $this->paymentrequisition->with('approvals','documents','purchaserequisitionaward.purchaserequisition','department','createdby')->where('source',$source)->where('source_id',$source_id)->get();
    }
}
