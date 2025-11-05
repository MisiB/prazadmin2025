<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workshoporder extends Model
{
    use HasFactory;

    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'workshop_id',
        'name',
        'surname', 
        'email',
        'phone',
        'delegates',
        'currency_id',
        'amount',
        'exchangerate_id',
        'customer_id',
        'ordernumber',
        'invoicenumber',
        'documenturl',
        'status'
    ];

    public function delegatelist():HasMany
    {
        return $this->hasMany(WorkshopDelegate::class,"workshoporder_id","id");
    }

    public function invoice():HasOne
    {
        return $this->hasOne(Invoice::class,"invoicenumber","invoicenumber");
    }

    public function exchangerate():BelongsTo
    {
        return $this->belongsTo(Exchangerate::class,"exchangerate_id","id");
    }

    public function workshop():BelongsTo
    {
        return $this->belongsTo(Workshop::class,"workshop_id","id");
    }

   

    public function currency():BelongsTo
    {
        return $this->belongsTo(Currency::class,"currency_id","id");
    }

    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class,"customer_id","id");
    }

    // Keep account() method for backward compatibility but point to customer
    public function account():BelongsTo
    {
        return $this->customer();
    }
}
