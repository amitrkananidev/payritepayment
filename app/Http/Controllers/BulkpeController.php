<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;
use Log;

use App\Models\User;
use App\Models\Addresses;
use App\Models\eko_services;
use App\Models\kyc_docs;
use App\Models\cities;
use App\Models\states;
use App\Models\dmt_customers;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;

class BulkpeController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        
    }
    
    public function bankVerification($data) {
        $api_url = "https://api.bulkpe.in/client/bankVerificationSync";
        Log::channel('bulkpeapi')->info("REQUEST");
        Log::channel('bulkpeapi')->info($data);
        $api_response = $this->ApiCalls->bulkpePostCall($api_url,$data);
        Log::channel('bulkpeapi')->info("RESPONSE");
        Log::channel('bulkpeapi')->info($api_response);
        return $api_response;
    }
    
    public function payoutTransaction($data) {
        $api_url = "https://api.bulkpe.in/client/initiatepayout";
        Log::channel('bulkpeapi')->info("REQUEST");
        Log::channel('bulkpeapi')->info($data);
        $api_response = $this->ApiCalls->bulkpePostCall($api_url,$data);
        Log::channel('bulkpeapi')->info("RESPONSE");
        Log::channel('bulkpeapi')->info($api_response);
        return $api_response;
        
    }
    
    public function createUPI($data) {
        $api_url = "https://api.bulkpe.in/client/createDynamicVpa";
        Log::channel('bulkpeapi')->info("REQUEST");
        Log::channel('bulkpeapi')->info($data);
        $api_response = $this->ApiCalls->bulkpePostCall($api_url,$data);
        Log::channel('bulkpeapi')->info("RESPONSE");
        Log::channel('bulkpeapi')->info($api_response);
        return $api_response;
        
    }
}
