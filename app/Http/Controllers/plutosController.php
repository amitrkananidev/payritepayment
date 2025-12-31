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
use App\Models\shop_details;

class PlutosController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        
    }
    
    public function apiAuth($data)
    {
        
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes='.$data;
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST apiAuth');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $response = $this->ApiCalls->plutosPostAuth($url,$string);
        log::channel('plutos')->info('RESPONSE apiAuth');
        log::channel('plutos')->info($response);
        $res = json_decode($response);
        $return['access_token'] = $res->access_token;
        return $return;
    }
    
    public function getCategories($data)
    {
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes=get_biller_categories';
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST getCategories');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $token = $this->apiAuth('get_biller_categories');
        log::channel('plutos')->info($token);
        $url = "https://uat-cou-switch-idfc.plutos.one/api/v1/billers/categories/all";
        $response = $this->ApiCalls->plutosGet($url,$token['access_token']);
        log::channel('plutos')->info('RESPONSE getCategories');
        log::channel('plutos')->info($response);
        return $response;
    }
    
    public function getBiller($data)
    {
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes=get_biller_by_category';
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST get_biller_by_category');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $token = $this->apiAuth('get_biller_by_category');
        log::channel('plutos')->info($token);
        $url = "https://uat-cou-switch-idfc.plutos.one/api/v1/billers/category/".urlencode($data);
        $response = $this->ApiCalls->plutosGet($url,$token['access_token']);
        log::channel('plutos')->info('RESPONSE get_biller_by_category');
        log::channel('plutos')->info($response);
        return $response;
    }
    
    public function getBillerByID($data)
    {
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes=read_bills read_plans read_packs read_billers read_regions bill_validate read_operators raise_complaint get_biller_plans read_transactions register_complain get_biller_by_region read_operator_circle check_complain_status get_biller_categories get_biller_by_category read_biller_categories bill_payment_validation get_bill_payment_txn_status pay_bill_int get_bill_int prepaid_bill_get_regions prepaid_bill_fetch_plans prepaid_bill_get_operator prepaid_bill_check_operator prepaid_bill_complaint_status prepaid_bill_complaint prepaid_bill_payment_txn_status prepaid_bill_payment_txn check_complaint_status create_transactions';
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST get_biller_by_category');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $token = $this->apiAuth('read_bills read_plans read_packs read_billers read_regions bill_validate read_operators raise_complaint get_biller_plans read_transactions register_complain get_biller_by_region read_operator_circle check_complain_status get_biller_categories get_biller_by_category read_biller_categories bill_payment_validation get_bill_payment_txn_status pay_bill_int get_bill_int prepaid_bill_get_regions prepaid_bill_fetch_plans prepaid_bill_get_operator prepaid_bill_check_operator prepaid_bill_complaint_status prepaid_bill_complaint prepaid_bill_payment_txn_status prepaid_bill_payment_txn check_complaint_status create_transactions');
        log::channel('plutos')->info($token);
        $url = "https://uat-cou-switch-idfc.plutos.one/api/v1/biller/".$data;
        $response = $this->ApiCalls->plutosGet($url,$token['access_token']);
        log::channel('plutos')->info('RESPONSE get_biller_by_category');
        log::channel('plutos')->info($response);
        return $response;
    }
    
    public function getFetch($data)
    {
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes=get_bill_int';
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST get_bill_int');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $token = $this->apiAuth('get_bill_int');
        log::channel('plutos')->info($token);
        $url = "https://uat-cou-switch-idfc.plutos.one/api/v1/bill/fetch/int";
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($data);
        $response = $this->ApiCalls->plutosPost($url,$token['access_token'],$data);
        log::channel('plutos')->info('RESPONSE get_bill_int');
        log::channel('plutos')->info($response);
        return $response;
    }
    
    public function PayBill($data)
    {
        $string = 'clientKey=5a2c12df-95d3-4490-a4b5-f18e10f2f248&clientSecret=$2b$10$8JVi5iOHajyor4Oc3G5velxQWle5adIcve15fXQiOtGWqQJO5mpLL&scopes=pay_bill_int';
        $url = "https://uat-cou-apiauth-idfc.plutos.one/v1/auth/token";
        log::channel('plutos')->info('REQUEST pay_bill_int');
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($string);
        $token = $this->apiAuth('pay_bill_int');
        log::channel('plutos')->info($token);
        $url = "https://uat-cou-switch-idfc.plutos.one/api/v1/bill/payment/int";
        log::channel('plutos')->info($url);
        log::channel('plutos')->info($data);
        $response = $this->ApiCalls->plutosPost($url,$token['access_token'],$data);
        log::channel('plutos')->info('RESPONSE pay_bill_int');
        log::channel('plutos')->info($response);
        return $response;
    }
}
