<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paymentrequisitiondocument extends Model
{
    protected $table = 'paymentrequisitiondocuments';

    protected $fillable = [
        'paymentrequisition_id',
        'document_type',
        'document',
        'filepath',
    ];

    public function paymentRequisition(): BelongsTo
    {
        return $this->belongsTo(PaymentRequisition::class, 'paymentrequisition_id');
    }
}
