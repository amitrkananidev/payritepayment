<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_commissions extends Model
{
    use HasFactory;
    
    // If you have custom date casting requirements, specify them in $dates
    protected $dates = ['created_at', 'updated_at'];
    
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
}
