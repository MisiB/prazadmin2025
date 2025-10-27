<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibudgetInterface;
use App\Models\Budget;
use App\Models\Budgetitem;
use App\Models\Budgetvirement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class _budgetRepository implements ibudgetInterface
{
    /**
     * Create a new class instance.
     */
    protected $budget;
    protected $budgetitem;
    protected $budgetvirement;
    public function __construct(Budget $budget,Budgetitem $budgetitem,Budgetvirement $budgetvirement)
    {
        $this->budget = $budget;
        $this->budgetitem = $budgetitem;
        $this->budgetvirement = $budgetvirement;
    }
 
    public function getbudgets()
    {
        return $this->budget->with("createdby","updatedby","deletedby","approvedby","currency")->get();
    }
    public function getbudget($id)
    {
        return $this->budget->find($id);
    }

    public function getbudgetbyuuid($uuid)
    {
        return $this->budget->with("createdby","updatedby","deletedby","approvedby","currency",'budgetitems.sourceoffund','budgetitems.department','budgetitems.expensecategory','budgetitems.strategysubprogrammeoutput','budgetitems.currency','budgetitems.incomingvirements','budgetvirements.from_budgetitem','budgetvirements.to_budgetitem','budgetvirements.createdby','budgetvirements.approvedby')->where("uuid", $uuid)->first();
    }
    public function createbudget($data)
    {
        try{
        $data["uuid"] = Str::uuid()->toString();
        $data["created_by"] = Auth::user()->id;
        $exist = $this->budget->where("year", $data["year"])->exists();
        if($exist){
            return ["status"=>"error","message"=>"Budget Year Already Exists"];
        }
        $this->budget->create($data);
        return ["status"=>"success","message"=>"Budget Created Successfully"];
    }catch(Exception $e){
        return ["status"=>"error","message"=>"Budget Not Created"]; 
    }
    }
    public function updatebudget($id, $data)
    {
        try{
            $this->budget->find($id)->update($data);
            return ["status"=>"success","message"=>"Budget Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>"Budget Not Updated"];
        }
    }
    public function deletebudget($id)
    {
        try{
            $this->budget->find($id)->delete();
            return ["status"=>"success","message"=>"Budget Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>"Budget Not Deleted"];
        }
    }
    public function approvebudget($id)
    {
        try{
            $budget = $this->budget->find($id);
            $budget->approved_by = Auth::user()->id;
            // Update all budget items for this budget
            $this->budgetitem->where('budget_id', $id)->update(["status" => "APPROVED"]);
            $budget->update(["status" => "APPROVED"]);
            return ["status" => "success", "message" => "Budget Approved Successfully"];
        }catch(Exception $e){
            return ["status" => "error", "message" => "Budget Not Approved"];
        }
    }


    public function  getbudgetitems($budget_id){

        return $this->budgetitem->with("currency","expensecategory","department","sourceoffund","budget","strategysubprogrammeoutput")->where("budget_id",$budget_id)->get();

    }
    public function getbudgetitembyuuid($uuid){
        return $this->budgetitem->with("currency","expensecategory","department","sourceoffund","budget","strategysubprogrammeoutput","incomingvirements","outgoingvirements","purchaserequisitions")->where("uuid",$uuid)->first();
    }
    public function getbudgetitemsbydepartment($budget_id,$department_id){
      return $this->budgetitem->with("currency","expensecategory","department","sourceoffund","budget","strategysubprogrammeoutput","incomingvirements","outgoingvirements","purchaserequisitions")->where("budget_id",$budget_id)->where("department_id",$department_id)->get();

    }
    public function createbudgetitem(array $data){

        try{
            $data["uuid"] = Str::uuid()->toString();
            $data["created_by"] = Auth::user()->id;
            $this->budgetitem->create($data);
            return ["status"=>"success","message"=>"Budget Item Created Successfully"];

        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }

    }
    public function updatebudgetitem($id, array $data){

        try{
            $data["updated_by"] = Auth::user()->id;
            $this->budgetitem->find($id)->update($data);
            return ["status"=>"success","message"=>"Budget Item Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletebudgetitem($id){

        try{
           $record= $this->budgetitem->find($id)->first();
           $record->deleted_by = Auth::user()->id;
           $record->delete();
            return ["status"=>"success","message"=>"Budget Item Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getbudgetitem($id){
        return $this->budgetitem->with("purchaserequisitions","outgoingvirements","incomingvirements")->where("id",$id)->first();

    }
    public function approvebudgetitem($id){
        try{
            $this->budgetitem->find($id)->update(["status"=>"APPROVED"]);
            return ["status"=>"success","message"=>"Budget Item Approved Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getbudgetvirements($budget_id){
        return $this->budgetvirement->with("budget","department","from_budgetitem","to_budgetitem","createdby","approvedby")->where("budget_id",$budget_id)->get(); 
    }
    public function createbudgetvirement(array $data){
        try{
            $data["uuid"] = Str::uuid()->toString();
            $data["user_id"] = Auth::user()->id;
            $this->budgetvirement->create($data);
            return ["status"=>"success","message"=>"Budget Virement Created Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updatebudgetvirement($id, array $data){
        try{
            $data["user_id"] = Auth::user()->id;
            $this->budgetvirement->find($id)->update($data);
            return ["status"=>"success","message"=>"Budget Virement Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletebudgetvirement($id){
        try{
           $record= $this->budgetvirement->find($id)->first();
           $record->deleted_by = Auth::user()->id;
           $record->delete();
            return ["status"=>"success","message"=>"Budget Virement Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getbudgetvirement($id){
        return $this->budgetvirement->find($id);
    }
    public function approvebudgetvirement($id){
        try{
            $this->budgetvirement->find($id)->update(["status"=>"APPROVED"]);
            return ["status"=>"success","message"=>"Budget Virement Approved Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function rejectbudgetvirement($data){
        try{
            $record= $this->budgetvirement->where("id", $data["id"])->first();
            $record->status = "REJECTED";
            $record->comment = $data["comment"];
            $record->save();    
            return ["status"=>"success","message"=>"Budget Virement Rejected Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    

}
