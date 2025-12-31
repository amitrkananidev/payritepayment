<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class transactions_cc extends Model
{
    use HasFactory;
    
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
    
    // Get mutator for updated_at
    public function getUpdatedAtAttribute($value)
    {
        // Return the date in ISO 8601 format with 6 decimal places
        return Carbon::parse($value)->setTimezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
    
    public function senders()
    {
        return $this->hasOne(CcSenders::class,'sender_id','sender_id');
    }
    
    public function beneficiaries()
    {
        return $this->hasOne(CcBeneficiaries::class,'beneficiary_id','beneficiary_id');
    }
}
