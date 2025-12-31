<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Authenticatable implements Wallet
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function findForPassport($mobile)
    {
        return $this->where('mobile', $mobile)->first();
    }

    public function userType()
    {
        // Logic to determine user type
        return $this->user_type;
    }
    
    public function getRetailers()
    {
        return $this->hasMany(user_levels::class,'toplevel_id');
    }
    
    public function userLevel()
    {
        return $this->hasOne(user_levels::class,'user_id');
    }
    
    public function shopDetail()
    {
        return $this->hasOne(shop_details::class,'user_id');
    }
    
    public function addresses()
    {
        return $this->hasOne(Addresses::class,'user_id');
    }
    
    public function kycDocs()
    {
        return $this->hasOne(kyc_docs::class,'user_id');
    }
    
    public function transactionsDmt()
    {
        return $this->hasMany(transactions_dmt::class, 'user_id', 'id');
    }
    
    public function transactionsAeps()
    {
        return $this->hasMany(transactions_aeps::class, 'user_id', 'id');
    }

    public function commissions()
    {
        return $this->hasMany(user_commissions::class, 'user_id', 'id');
    }
    
    public function beneficiariesUpi()
    {
        return $this->hasMany(dmt_upi_beneficiaries::class, 'user_id');
    }
}
