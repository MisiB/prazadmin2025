<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchaserequisition extends Model
{
    protected $casts = [
        'comments' => 'collection'
    ];
    
    public function budgetitem()
    {
        return $this->belongsTo(Budgetitem::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedby()
    {
        return $this->belongsTo(User::class,"requested_by","id");
    }

    public function recommendedby()
    {
        return $this->belongsTo(User::class,"recommended_by","id");
    }
    public function workflow()
    {
        return $this->belongsTo(Workflow::class,"workflow_id","id");
    }

    public function approvals()
    {
        return $this->hasMany(Purchaserequisitionapproval::class);
    }

    public function awards()
    {
        return $this->hasMany(Purchaserequisitionaward::class);
    }

    
}
