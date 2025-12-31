<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
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
use App\Models\ace_money_tokens;

class AceMoneyController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        
    }
    
    public function getCustomer($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        
        $url = "https://aceneobank.com/apiService/V3/dmt/get-customer";
        log::channel('acemoney')->info('REQUEST getCustomer');
        log::channel('acemoney')->info($url);
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE getCustomer');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function getCustomerBalance($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/customer-limit";
        log::channel('acemoney')->info('REQUEST getCustomerBalance');
        log::channel('acemoney')->info($url);
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE getCustomerBalance');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    
    public function getBeneficiary($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $decode = json_decode($data);
        $url = "https://aceneobank.com/apiService/V2/dmt/getBeneficiers?agent_id=".$decode->agentid."&senderno=".$decode->CustomerMobileNo;
        log::channel('acemoney')->info('REQUEST getBeneficiary');
        log::channel('acemoney')->info($url);
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoneyGet($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE getBeneficiary');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function ekyc($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/processCustomerEKYC";
        log::channel('acemoney')->info('REQUEST ekyc');
        log::channel('acemoney')->info($url);
        
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoneyKyc($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE ekyc');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function createCustomerOTP()
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/customer-otp";
        log::channel('acemoney')->info('REQUEST createCustomerOTP');
        log::channel('acemoney')->info($url);
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE createCustomerOTP');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function createCustomer($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/create-customer";
        log::channel('acemoney')->info('REQUEST createCustomer');
        log::channel('acemoney')->info($url);
        
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE createCustomer');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function addBeneficiary($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V2/dmt/AddBeneficiar";
        log::channel('acemoney')->info('REQUEST createCustomer');
        log::channel('acemoney')->info($url);
        
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE createCustomer');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function OTPGenerationTxn($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/generateTxnOtp";
        log::channel('acemoney')->info('REQUEST createCustomer');
        log::channel('acemoney')->info($url);
        
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE createCustomer');
        log::channel('acemoney')->info($response);
        return $response;
    }
    
    public function FundTransfer($data)
    {
        $gettoken = ace_money_tokens::where('created_at', '>=', Carbon::now()->subHours(12))->orderBy('id','DESC')->first();
        if($gettoken){
            $header_token = $gettoken->token;
        }else{
            $tokens = $this->ApiCalls->AceMoneyToken();
            $token = json_decode($tokens);
            $header_token = $token->accessToken;
            
            $instoken = new ace_money_tokens();
            $instoken->token = $header_token;
            $instoken->save();
        }
        $url = "https://aceneobank.com/apiService/V3/dmt/transaction";
        log::channel('acemoney')->info('REQUEST createCustomer');
        log::channel('acemoney')->info($url);
        
        log::channel('acemoney')->info($data);
        log::channel('acemoney')->info($header_token);
        $response = $this->ApiCalls->AceMoney($url,$data,$header_token);
        log::channel('acemoney')->info('RESPONSE createCustomer');
        log::channel('acemoney')->info($response);
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
