<?php

namespace App\Classes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Illuminate\Support\Facades\Hash;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use Auth;
use DB;

use App\Models\User;
use App\Models\kyc_docs;
use App\Models\user_devices;

class ApiCalls
{
    public function ipaydigitalPost($data) {
        log::channel('plutos')->info($data);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.ipaydigital.co.in/DMT/v1/MoneyTransfer',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            'KeyData: {"Mobile":"9978177178","APIToken":"d1dd576eafca4ba1a7d7"}',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        log::channel('plutos')->info($response);
        return $response;
    }
    
    public function plutosPost($url,$token,$data) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: '.$token
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function plutosGet($url,$token) {
        
        log::channel('plutos')->info($url);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "$url",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: '.$token
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function plutosPostAuth($url,$post) {
        
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $post,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function mplan($url) {
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $Get_Response = curl_exec($ch);
        curl_close($ch);
        return $Get_Response;
    }
    
    public function rkwalletRecharge($data) {
        $url = 'www.rkwallet.in/Admin/RechargeAPI.aspx?UserID=9978177178&Password=43891910&MobileNo=9978177178&Message='.$data;
        log::channel('rkwallet')->info('request');
        log::channel('rkwallet')->info($url);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Cookie: ASP.NET_SessionId=5s4f3hd2to0ets3ujitr0f1e'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        log::channel('rkwallet')->info('response');
        log::channel('rkwallet')->info($response);
        
        return $response;
    }
    
    public function AceMoneyToken() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://aceneobank.com/apiService/apiLogin',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('agentID' => 'vinuparmar1986@gmail.com','agentSecret' => 'Vinod@6162'),
          CURLOPT_HTTPHEADER => array(
            'clientId: 57894860302092277782928600569780_PGZgdqn6AVdmB/VRivN2zZvPgKHhbVjPt1Jrs1VBfiU=',
            'ClientSecret: 83681378020233148941196559791014_6yr9OmS/6EnvP0PQ8aAWRif+ZVm/G9c252b/03pcSb8=',
            'apiKey: 33829849134989186840188415444042_r5WAW3r6pdBrdt9tlsfrvdQfUF5nC24yjnQ7m0SbxL2Jxh9DW7ZXCktbt3vMdyAkGcHLLXRlbsNzfXEkBX1jvlSZLWFKD0Af1AmnDl9zwN+6w87Q9tzElJuae9JUYObi',
            'Cookie: neobank_dev_session=vIy6A4xqiNIkZaQe5WlL5oRvcrUhC141yrQxuGiS'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        log::channel('acemoney')->info('REQUEST Token');
        log::channel('acemoney')->info($response);
        
        return $response;
    }
    
    public function AceMoney($url,$params,$bearer) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'clientId: 57894860302092277782928600569780_PGZgdqn6AVdmB/VRivN2zZvPgKHhbVjPt1Jrs1VBfiU=',
            'ClientSecret: 83681378020233148941196559791014_6yr9OmS/6EnvP0PQ8aAWRif+ZVm/G9c252b/03pcSb8=',
            'apiKey: 33829849134989186840188415444042_r5WAW3r6pdBrdt9tlsfrvdQfUF5nC24yjnQ7m0SbxL2Jxh9DW7ZXCktbt3vMdyAkGcHLLXRlbsNzfXEkBX1jvlSZLWFKD0Af1AmnDl9zwN+6w87Q9tzElJuae9JUYObi',
            'Authorization: Bearer '.$bearer,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function AceMoneyGet($url,$params,$bearer) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'clientId: 57894860302092277782928600569780_PGZgdqn6AVdmB/VRivN2zZvPgKHhbVjPt1Jrs1VBfiU=',
            'ClientSecret: 83681378020233148941196559791014_6yr9OmS/6EnvP0PQ8aAWRif+ZVm/G9c252b/03pcSb8=',
            'apiKey: 33829849134989186840188415444042_r5WAW3r6pdBrdt9tlsfrvdQfUF5nC24yjnQ7m0SbxL2Jxh9DW7ZXCktbt3vMdyAkGcHLLXRlbsNzfXEkBX1jvlSZLWFKD0Af1AmnDl9zwN+6w87Q9tzElJuae9JUYObi',
            'Authorization: Bearer '.$bearer,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function AceMoneyKyc($url,$params,$bearer) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'clientId: 57894860302092277782928600569780_PGZgdqn6AVdmB/VRivN2zZvPgKHhbVjPt1Jrs1VBfiU=',
            'ClientSecret: 83681378020233148941196559791014_6yr9OmS/6EnvP0PQ8aAWRif+ZVm/G9c252b/03pcSb8=',
            'apiKey: 33829849134989186840188415444042_r5WAW3r6pdBrdt9tlsfrvdQfUF5nC24yjnQ7m0SbxL2Jxh9DW7ZXCktbt3vMdyAkGcHLLXRlbsNzfXEkBX1jvlSZLWFKD0Af1AmnDl9zwN+6w87Q9tzElJuae9JUYObi',
            'Authorization: Bearer '.$bearer,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function payPointWithoutAuth($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'AppID: PAYRITEPS',
            'AuthKey: /@v{NeSE9dXxZ:nvfQC:pP}R]#td>#TFS870:0E^6/b6raju3!',
            'SecretKey: J3-F:_=eBn=}I?JSf3X[2*LZYr{U@yc~@Y21XM^[^0y3:&}ewZ',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function payPoint($url,$params, $bearer) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'AppID: PAYRITEPS',
            'AuthKey: /@v{NeSE9dXxZ:nvfQC:pP}R]#td>#TFS870:0E^6/b6raju3!',
            'SecretKey: J3-F:_=eBn=}I?JSf3X[2*LZYr{U@yc~@Y21XM^[^0y3:&}ewZ',
            'Authorization: Bearer '.$bearer,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayPutCallUAT($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Authorization: f5161079-4b02-446c-aacf-357d32f28f82',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayUpiPostCall($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Authorization: ab2877a5-c65e-4181-abb8-e6cb2d9255bd',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayAepsPostCall($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Authorization: d8a62377-de0b-410d-9e3e-c4033bf53165',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayAepsGetCall($url) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: d8a62377-de0b-410d-9e3e-c4033bf53165',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayPostCall($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Authorization: 8c2724ee-ecdb-412b-9680-89e90eac5015',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function credoPayPutCall($url,$params) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Authorization: 8c2724ee-ecdb-412b-9680-89e90eac5015',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    public function cyrusPostCall($url,$params) {
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $params,
          CURLOPT_HTTPHEADER => array(
            'Cookie: ASP.NET_SessionId=eepot3vpvwyj3rgefwirz522'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;

    }
    
    public function bulkpePostCall($url,$params) {
        log::channel('bulkpeapi')->info('REQUEST');
        log::channel('bulkpeapi')->info($url);
        log::channel('bulkpeapi')->info($params);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer aWSVQNyt+z3IiJHV+YX9UtNs7idZDgBq9LptIZpqAwE5PDFW0QgUgfJjB3zkwsMRkD6PDL+w4LQyTFy0eK/11A=='
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        log::channel('bulkpeapi')->info('RESPONSE');
        log::channel('bulkpeapi')->info($response);
        return $response;

    }
    
    public function bulkpePostCallFile($url,$params) {
        log::channel('bulkpeapi')->info('REQUEST');
        log::channel('bulkpeapi')->info($url);
        log::channel('bulkpeapi')->info($params);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
                "Content-Type: multipart/form-data",
            'Authorization: Bearer aWSVQNyt+z3IiJHV+YX9UtNs7idZDgBq9LptIZpqAwE5PDFW0QgUgfJjB3zkwsMRkD6PDL+w4LQyTFy0eK/11A=='
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        log::channel('bulkpeapi')->info('RESPONSE');
        log::channel('bulkpeapi')->info($response);
        return $response;

    }
    
    
    public function ekoPutCall($url,$params) {
        
        // print_r($params);
        
        // exit;
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $api_url = env('EKO_AEPS_URL')."service/activate";
        if($api_url == $url) {
            
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: multipart/form-data",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }
        else {
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }  
        Log::channel('ekoapi')->info("Header");
            Log::channel('ekoapi')->info($headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $api_response = curl_exec ($ch);
        $curl = curl_init();
        curl_close($curl);
        
        
        return $api_response;
    }
    
    public function ekoPostCall($url,$params) {
        
        // print_r($params);
        // print_r($url);
        
        // exit;
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $api_url = env('EKO_AEPS_URL')."service/activate";
        if($api_url == $url) {
            
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: multipart/form-data",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }
        else {
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $api_response = curl_exec ($ch);
        $curl = curl_init();
        curl_close($curl);
        
        return $api_response;
    }
    
    public function ekoGetWithParaCall($url,$params) {
        
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
        
        $headers = [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: api.eko.in:25002",
            "cache-control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
            "secret-key: ".$secret_key,
            "secret-key-timestamp: ".$secret_key_timestamp
        ];
        Log::channel('ekoapi')->info("HEADER");
        Log::channel('ekoapi')->info($headers);
            
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $params,
          CURLOPT_HTTPHEADER => $headers,
        ));
        
        $api_response = curl_exec($curl);
        
        curl_close($curl);

        
        return $api_response;
    }
    
    public function ekoGetCall($url) {
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: api.eko.in:25002",
            "cache-control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
            "secret-key: ".$secret_key,
            "secret-key-timestamp: ".$secret_key_timestamp
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $api_response = curl_exec($ch);
        curl_close($ch);

        return $api_response;
    }
    
    public function ekoDeleteCall($url) {
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $headers = [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: api.eko.in:25002",
            "cache-control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
            "secret-key: ".$secret_key,
            "secret-key-timestamp: ".$secret_key_timestamp
        ];
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'DELETE',
          CURLOPT_POSTFIELDS => 'initiator_id='.$initiator_id,
          CURLOPT_HTTPHEADER => $headers,
        ));
        
        $api_response = curl_exec($curl);
        
        curl_close($curl);

        return $api_response;
    }
    
    public function payritePostCall($url) {
        // $url = urlencode($url);
        log::info('PAYRITE REQUEST:');
        log::info($url);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        log::info('PAYRITE RESPONSE:');
        log::info($response);
        return $response;
    }
    
    public function payritePostCallWithParam($url,$data) {
        // $url = urlencode($url);
        log::info('PAYRITE REQUEST:');
        log::info($url);
        log::info($data);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
        ));
        
        $response = curl_exec($curl);
        log::info('PAYRITE RESPONSE:');
        log::info($response);
        return $response;
    }
    
    public function smsgatewayhubGetCall($mobile,$text,$dlttemplateid) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=GPL5rrc7gEKINfbBEHcUNw&senderid=PAYRIT&channel=2&DCS=0&flashsms=0&number=$mobile&text=$text&route=31&EntityId=1201170927822604813&dlttemplateid=$dlttemplateid",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        Log::channel('smsapi')->info($response);
        return $response;
    }
    
    public function sendfcmNotification($user_id, $title, $message) {
        $user = user_devices::select('device')->where('user_id',$user_id)->whereNotNull('device')->orderBy('id','DESC')->first();
        if($user) 
        {
            
            
                $token = $user->device;
                
                $firebase = (new Factory)
                ->withServiceAccount(public_path(env('FIREBASE_CREDENTIALS')));
            
                $messaging = $firebase->createMessaging();
                
                $notification = Notification::create($title, $message);
                
                $message = CloudMessage::withTarget('token', $token)->withNotification($notification);
                    
                
                $messaging->send($message);
                
            
            
            return true;
        }
         return false; // No user devices found or none of them have valid tokens
    }
    
    public function billAvenuePostCall($url) {
        // $url = urlencode($url);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function billAvenuePostCallWithParam($url,$data) {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/plain'
          ),
        ));
        
        $response = curl_exec($curl);
        
        return $response;
    }
}
?>