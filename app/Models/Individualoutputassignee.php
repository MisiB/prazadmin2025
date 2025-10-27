<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Individualoutputassignee extends Model
{
    
    public function individualoutput(){
        return $this->belongsTo(Individualoutput::class,"individualoutput_id");
    }
    public function user(){
        return $this->belongsTo(User::class,"user_id");
    }
}
