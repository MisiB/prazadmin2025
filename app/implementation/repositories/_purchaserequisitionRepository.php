<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use App\Models\Departmentuser;
use App\Models\Purchaserequisition;
use App\Models\Purchaserequisitionapproval;
use App\Models\Purchaserequisitionaward;
use App\Models\Purchaserequisitionawarddocument;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\AwaitingdeliveryNotification;
use App\Notifications\PurchaseRequisitionAlert;
use App\Notifications\PurchaseRequisitionNotification;
use App\Notifications\PurchaseRequisitionUpdate;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _purchaserequisitionRepository implements ipurchaseerequisitionInterface
{
    /**
     * Create a new class instance.
     */
    protected $purchaserequisition;
    protected $purchaserequisitionapproval;
    protected $purchaserequisitionaward;
    protected $purchaserequisitionawarddocument;
    protected $workflow;
    protected $budgetrepo;
    protected $departmentuser;
    public function __construct(Purchaserequisition $purchaserequisition,Purchaserequisitionapproval $purchaserequisitionapproval,Purchaserequisitionaward $purchaserequisitionaward,Purchaserequisitionawarddocument $purchaserequisitionawarddocument,Workflow $workflow,ibudgetInterface $budgetrepo,Departmentuser $departmentuser)
    {
        $this->purchaserequisition = $purchaserequisition;
        $this->purchaserequisitionapproval = $purchaserequisitionapproval;
        $this->purchaserequisitionaward = $purchaserequisitionaward;
        $this->purchaserequisitionawarddocument = $purchaserequisitionawarddocument;
        $this->workflow = $workflow;
        $this->budgetrepo = $budgetrepo;
        $this->departmentuser = $departmentuser;
    }
    public function getpurchaseerequisitions($year){
        return $this->purchaserequisition->with('budgetitem.currency','department','requestedby','recommendedby','workflow.workflowparameters.permission')->whereNotIn('status',['AWAITING_RECOMMENDATION','PENDING','NOT_RECOMMENDED'])->where('year', $year)->paginate(10);
    }
    public function getpurchaseerequisition($id){
        return $this->purchaserequisition->with('budgetitem.currency','department','requestedby','recommendedby','awards.customer','awards.currency','awards.documents','awards.createdby','awards.approvedby')->find($id);
    }
    public function getpurchaseerequisitionbyuuid($uuid){
        return $this->purchaserequisition->with('budgetitem.currency','department','requestedby','recommendedby','workflow.workflowparameters','approvals.user')->where('uuid', $uuid)->first();
    }
    public function getpurchaseerequisitionbydepartment($year,$department_id){
        return $this->purchaserequisition->with('budgetitem.currency','department','requestedby','recommendedby')->where('year', $year)->where('department_id', $department_id)->paginate(10);
    }
    public function createpurchaseerequisition($data){
        try{
        $budgetitem = $this->budgetrepo->getbudgetitem($data["budgetitem_id"]);
        $budgetitem_amount = $budgetitem->total;
        $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum("amount");
        $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum("amount");
        $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->sum("quantity") * $budgetitem->unitprice;
        $budgetitem_total = $budgetitem_amount - $budgetitem_outgoingvirements + $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;
     
        $purchaserequisition_total = $budgetitem->unitprice * $data["quantity"];
        $prnumber = "PR".date("Y").random_int(1000,9999999);
        if($budgetitem_total < $purchaserequisition_total){
            return ["status"=>"error","message"=>"Budget Item Total is Less than Amount"];
        }
        $workflowname = config("workflow.purchase_requisitions");
        $workflow = $this->workflow->where("name", $workflowname)->first();
        if($workflow==null){
            return ["status"=>"error","message"=>"Workflow Not Defined"];
        }
        $data["uuid"] = Str::uuid()->toString();
        $data["requested_by"] = Auth::user()->id;
        $data['workflow_id'] = $workflow->id;
        $data["year"] = date("Y");
        $data["prnumber"] = $prnumber;
        $data['department_id'] = $budgetitem->department_id;
        $data["status"]="AWAITING_RECOMMENDATION";
        $this->purchaserequisition->create($data);
        $departmentuser = $this->departmentuser->with("supervisor")->where("user_id",Auth::user()->id)->first();
        $array = [];
        $array["budgetitem"] = $budgetitem->activity;
        $array["purpose"] = $data["purpose"];
        $array["quantity"] = $data["quantity"];
        $array["unitprice"] = $budgetitem->unitprice;
        $array["total"] = $purchaserequisition_total;
        $array['uuid'] = $data["uuid"];
        Notification::send($departmentuser->supervisor, new PurchaseRequisitionAlert(collect($array)));
       

        return ["status"=>"success","message"=>"Purchase Requisition Created Successfully"];
    }catch(Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
    }
    }
    public function updatepurchaseerequisition($id,$data){
        try{
        $budgetitem = $this->budgetrepo->getbudgetitem($data["budgetitem_id"]);
        $budgetitem_amount = $budgetitem->amount;
        $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum("amount");
        $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum("amount");
        $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->sum("quantity")*$budgetitem->unitprice;
        $budgetitem_total = $budgetitem_amount - $budgetitem_outgoingvirements - $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;
        $purchaserequisition_total = $budgetitem->unitprice * $data["quantity"];
        if($budgetitem_total < $purchaserequisition_total){
            return ["status"=>"error","message"=>"Budget Item Total is Less than Amount"];
        }
        $data["updated_by"] = Auth::user()->id;
        $this->purchaserequisition->find($id)->update($data);
        return ["status"=>"success","message"=>"Purchase Requisition Updated Successfully"];
    }catch(Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
    }
    }
    public function deletepurchaseerequisition($id){
        try{
           $record  = $this->purchaserequisition->where("id", $id)->first();
            if($record->status == "AWAITING_RECOMMENDATION"){
                $record->delete();
                return ["status"=>"success","message"=>"Purchase Requisition Deleted Successfully"];
            }else{
                return ["status"=>"error","message"=>"Purchase Requisition Cannot be Deleted"];
            }
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getpurchaseerequisitionbystatus($year,$status){
        return $this->purchaserequisition->with('budgetitem.currency','department','requestedby','recommendedby','approvals.user','awards.customer','awards.currency')->where("year", $year)->where("status", $status)->paginate(10);
    }

    public function recommend($id,$data){
        try{
            $record = $this->purchaserequisition->with('workflow.workflowparameters.permission')->where("id", $id)->first();
            if($record->status != "AWAITING_RECOMMENDATION"){
                return ["status"=>"error","message"=>"Purchase Requisition Cannot be Recommended"];
            }
            if($data["decision"] == "RECOMMEND"){
                $workflowparameter = $record->workflow->workflowparameters->where("order", 1)->first();
                $record->status = $workflowparameter->status;
                $record->recommended_by = Auth::user()->id;
                $record->save();
                $users = User::permission($workflowparameter->permission->name)->get();
              
                if($users->count() > 0){
                    $array = [];
                    $array["budgetitem"] = $record->budgetitem->activity;
                    $array["strategysubprogrammeoutput"] = $record->budgetitem->strategysubprogrammeoutput->output;
                    $array["department"] = $record->department->name;
                    $array["requested_by"] = $record->requested_by->name??"";
                    $array["recommended_by"] = $record->recommended_by->name??"";
                    $array["purpose"] = $record->purpose;
                    $array["quantity"] = $record->quantity;
                    $array["unitprice"] = $record->unitprice;
                    $array["total"] = $record->total;
                    $array["status"] = $workflowparameter->status;
                    $array['uuid'] = $record->uuid;
                    Notification::send($users, new PurchaseRequisitionNotification($array));
                }
            }else{
                $record->status = "NOT_RECOMMENDED";
                $comments = $record->comments;
                 $comments[] = ["user_id"=>Auth::user()->name,"comment"=>$data["comment"],"created_at"=>now()];
                 $record->comments = $comments;
                $record->save();
            }
      
            return ["status"=>"success","message"=>"Purchase Requisition Recommended Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function makedecision($id,$data){
        try{
            $record  = $this->purchaserequisition->with("workflow.workflowparameters.permission",'recommendedby','requestedby')->where("id", $id)->first();
            $array = [];
            $users=new Collection();
                    $array["budgetitem"] = $record->budgetitem->activity;
                    $array["strategysubprogrammeoutput"] = $record->budgetitem->strategysubprogrammeoutput->output;
                    $array["department"] = $record->department->name;
                    $array["requested_by"] = $record->requestedby->name??"";
                    $array["recommended_by"] = $record->recommendedby->name??"";
                    $array["purpose"] = $record->purpose;
                    $array["quantity"] = $record->quantity;
                    $array["unitprice"] = $record->unitprice;
                    $array["total"] = $record->total;
                    $array['uuid'] = $record->uuid;
                    $array["status"] = $record->status;
            $workflowparameter = $record->workflow->workflowparameters->where("status", $record->status)->first();
            if($data["decision"] == "APPROVED"){
                $count = $record->workflow->workflowparameters->count();
                if($workflowparameter->order+1 <= $count){
                    $payload = $record->workflow->workflowparameters->where("order", $workflowparameter->order+1)->first();
                     $status = $payload->status;
                     $permission = $payload->permission->name;
                    if($record->status == "BUDGET_CONFIRMATION"){
                        $record->fundavailable = "Y";
                    }
                    $record->status = $status;

                    $users = User::permission($permission)->get();
                }else{
                    $record->status = "AWAITING_PMU";
                    $users = User::permission($workflowparameter->permission->name)->get();
                }
              
                $record->save();

            
                if($users->count() > 0){
                   
                    Notification::send($users, new PurchaseRequisitionNotification($array));
                }
            }else{
                $record->status = "REJECTED";
                $record->save();
            }
                $this->purchaserequisitionapproval->create([
                    "purchaserequisition_id"=>$record->id,
                    'workflowparameter_id'=>$workflowparameter->id,
                    "user_id"=>Auth::user()->id,
                    "status"=>$data["decision"],
                    "comment"=>$data["comment"]
                ]);
                $array2 = [];
                $array2["step"] = $workflowparameter->status;
                $array2["status"] = $data["decision"];
                $array2["comment"] = $data["comment"];
                Notification::send($record->requestedby, new PurchaseRequisitionUpdate($array2));
                Notification::send($record->recommendedby, new PurchaseRequisitionUpdate($array2));
                if($data["decision"] != "APPROVED"){
                    $record->status = "REJECTED";
                    $record->save();
                }
                return ["status"=>"success","message"=>"Purchase Requisition Updated Successfully"];
            
                        
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    } 
    public function getpurchaserequeisitionbyworkflowparameter($year){
        $data = $this->workflow->with(["workflowparameters.permission","workflowparameters.purchaserequisitionapprovals"=>function($query)use($year){
            $query->where("year", $year);
            $query->whereNotIn("status", ["AWAITING_RECOMMENDATION","PENDING","NOT_RECOMMENDED"]);
        }])->where("name", "purchase_requisitions")->first();
        return $data->workflowparameters;
        
    }
    public function createaward($data){
        try{
            $data["created_by"] = Auth::user()->id;
            $data["uuid"] = Str::uuid()->toString();
            $this->purchaserequisitionaward->create($data);
            return ["status"=>"success","message"=>"Award Created Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateaward($id,$data){
        try{
            $this->purchaserequisitionaward->find($id)->update($data);
            return ["status"=>"success","message"=>"Award Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteaward($id){
        try{
            $this->purchaserequisitionaward->find($id)->delete();
            return ["status"=>"success","message"=>"Award Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getawards($year){
        return $this->purchaserequisitionaward->with('purchaserequisition.budgetitem.currency','purchaserequisition.department','purchaserequisition.requestedby','purchaserequisition.recommendedby','purchaserequisition.workflow.workflowparameters.permission','customer','currency')->where('year', $year)->paginate(10);
    }
    public function getaward($id){
        return $this->purchaserequisitionaward->with('purchaserequisition.budgetitem.currency','customer','purchaserequisition.department','purchaserequisition.requestedby','purchaserequisition.recommendedby','purchaserequisition.workflow.workflowparameters.permission','customer','currency','documents','createdby','approvedby')->find($id);
    }
    public function approveaward($id){
        try{
            $purchaserequisition = $this->purchaserequisition->where("id", $id)->first();
            foreach($purchaserequisition->awards as $award){
                $award->status = "APPROVED";
                $award->approved_by = Auth::user()->id;
                $award->save();
            }
            $purchaserequisition->status = "AWAITING_DELIVERY";
            $purchaserequisition->save();
            $users = User::permission("ADMIN.DELIVERY.ACCESS")->get();
            Notification::send($users, new AwaitingdeliveryNotification());
            return ["status"=>"success","message"=>"Award Approved Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function createawarddocument($data){
        try{
            $this->purchaserequisitionawarddocument->create($data);
            return ["status"=>"success","message"=>"Award Document Created Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateawarddocument($id,$data){
        try{
            $this->purchaserequisitionawarddocument->find($id)->update($data);
            return ["status"=>"success","message"=>"Award Document Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteawarddocument($id){
        try{
            $this->purchaserequisitionawarddocument->find($id)->delete();
            return ["status"=>"success","message"=>"Award Document Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getawarddocuments($id){
        return $this->purchaserequisitionawarddocument->where("purchaserequisitionaward_id", $id)->get();
    }
}
