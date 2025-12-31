<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class fund_onlines extends Model
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
}
