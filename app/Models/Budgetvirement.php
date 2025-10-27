<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budgetvirement extends Model
{
    public function budget(){
        return $this->belongsTo(Budget::class,"budget_id");
    }
    public function department(){
        return $this->belongsTo(Department::class,"department_id");
    }
    public function from_budgetitem(){
        return $this->belongsTo(Budgetitem::class,"from_budgetitem_id");
    }
    public function to_budgetitem(){
        return $this->belongsTo(Budgetitem::class,"to_budgetitem_id");
    }
    public function createdby(){
        return $this->belongsTo(User::class,"user_id");
    }
  
    public function approvedby(){
        return $this->belongsTo(User::class,"approved_by");
    }
}
