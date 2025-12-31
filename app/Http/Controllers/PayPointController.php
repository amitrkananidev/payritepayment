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

class PayPointController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        
    }
    
    /**
     * Display a listing of the resource.
     */
    public function generateOTP($data)
    {
        $url = "https://api.digikhata.in/p2a/v1/generateotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPointWithoutAuth($url,$data);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function verifyOTP($data)
    {
        $url = "https://api.digikhata.in/p2a/v1/verifyotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPointWithoutAuth($url,$data);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function generateAadhaarOTP($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/kyc/aadhaargenerateotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function resendAadhaarOTP($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/kyc/aadhaarresendotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function validateAadhaarOTP($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/kyc/aadhaarvalidateotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function pancardKYC($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/kyc/pancard";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function beneficiaryList($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/beneficiary/list";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function addBeneficiary($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/beneficiary/add";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function deleteBeneficiary($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/beneficiary/delete/getotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function deleteBeneficiaryOtp($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/beneficiary/delete/verifyotpanddelete";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function doTransaction($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/fundtransfer/getotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function doTransactionVerify($data,$token)
    {
        $url = "https://api.digikhata.in/p2a/v1/p2a/validateotpanddop2a";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPoint($url,$data,$token);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function refundRequest($data)
    {
        $url = "https://api.digikhata.in/p2a/v1/p2a/refund/generateotp";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPointWithoutAuth($url,$data);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
    
    public function refundRequestOTPverify($data)
    {
        $url = "https://api.digikhata.in/p2a/v1/p2a/refund/validateotpandrefund";
        Log::channel('paypoint')->info("REQUEST BODY");
        Log::channel('paypoint')->info($data);
        Log::channel('paypoint')->info("REQUEST");
        Log::channel('paypoint')->info($url);
        $api_response = $this->ApiCalls->payPointWithoutAuth($url,$data);
        Log::channel('paypoint')->info("RESPONSE");
        Log::channel('paypoint')->info($api_response);
        
        return $api_response;
    }
}
