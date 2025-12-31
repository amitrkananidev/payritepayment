<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function cities()
    {
        return $this->belongsTo(cities::class, 'city_id');
    }
    
    public function states()
    {
        return $this->belongsTo(states::class.'state_id');
    }
}
