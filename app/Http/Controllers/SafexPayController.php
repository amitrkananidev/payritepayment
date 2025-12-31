<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Classes\WalletCalculation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;
use Log;
use Exception;

use App\Models\banks;
use App\Models\User;
use App\Models\Addresses;
use App\Models\eko_services;
use App\Models\kyc_docs;
use App\Models\cities;
use App\Models\states;
use App\Models\dmt_customers;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;
use App\Models\transactions_aeps;

class SafexPayController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, WalletCalculation $WalletCalculation){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->WalletCalculation = $WalletCalculation;
        
    }
    
    function encryptDataold($key, $text) {
        // Ensure the key is 32 bytes long (256 bits)
        // $key = substr($key, 0, 32);
        
        // Encrypt the text using AES-256-ECB mode with PKCS7 padding
        // $encryptedText = openssl_encrypt($text, 'AES-256-CBC', base64_decode($key), OPENSSL_RAW_DATA, $iv);
        
        // Apply PKCS7 padding manually
        // $blockSize = 16;
        // $pad = $blockSize - (strlen($text) % $blockSize);
        // $text = $text;
        
        $pad = $blockSize - (strlen($text) % $blockSize);
        $padtext = $text . str_repeat(chr($pad), $pad);
        $iv = "0123456789abcdef";
        // Encrypt the padded text again
        $encryptedText = openssl_encrypt($padtext, 'AES-256-CBC', base64_decode($key), OPENSSL_RAW_DATA, $iv);
        
        // Convert to base64 to match the C# output
        return base64_encode($encryptedText);
    }
    
    function encryptData($key, $text, $type = 'aes-256-cbc') {
        // Generate a random IV
        $ivLength = openssl_cipher_iv_length($type);
        $iv = '0123456789abcdef';
        
        // Ensure key is properly decoded if base64 encoded
        $decodedKey = base64_decode($key);
        
        // Encrypt the data
        $encrypted = openssl_encrypt($text,$type,$decodedKey,OPENSSL_RAW_DATA,$iv);
        
        // Combine IV and encrypted data
        // We need to store the IV with the encrypted data so we can decrypt later
        $combined = $iv . $encrypted;
        
        // Return as base64 encoded string
        return base64_encode($encrypted);
    }
    
    function decryptData($key, $encryptedText, $type = 'aes-256-cbc') {
        // Decode the base64 encoded string
        $decoded = base64_decode($encryptedText);
        
        // Get the IV length
        $ivLength = openssl_cipher_iv_length($type);
        
        // Extract IV and encrypted data
        $iv = '0123456789abcdef';
        $encrypted = $decoded;
        
        // Ensure key is properly decoded if base64 encoded
        $decodedKey = base64_decode($key);
        
        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, $type, $decodedKey, OPENSSL_RAW_DATA, $iv);
        
        return $decrypted;
    }
    
    public function transactions($data)
    {   
        // $aadhar = $data['aadhaar_number'];
        // $aachard_enc = $this->encryptData("d8a62377de0b410d9e3ec4033bf53165",$aadhar);
        // $Hashed = md5($aadhar);
        // $masked = $this->maskAadhaarNumber($aadhar);
        // $pdata = $data['PidData'];
        // $cpid = $data['cpid'];
        // $srno = $data['srno'];
        // $rdsVer = $data['rdsVer'];
        // $rdsId = $data['rdsId'];
        // $mi = $data['mi'];
        
        // $amount = $data['amount'];
        // $transaction_type = $data['transaction_type'];
        // $bank_name = $data['bank_name'];
        // $user_id = $data['user_id'];
        // $transaction_id = $data['transaction_id'];
        
        
        Log::channel('safexpay')->info("REQUEST");
        Log::channel('safexpay')->info($data);
        
        $enc = $this->encryptData("xVHYj9hwm9J9f6AohY5Wc6TKNVhYnoLTSRoUM8/xqRQ=",$data);
        Log::channel('safexpay')->info($enc);
        $json = '{
                    "payload":"'.$enc.'",
                    "uId":"AGEN3580024871"
                }';
        
        
        // $url = "https://neodev2.safexpay.com/agWalletAPI/v2/agg";
        $url = "https://remittance.touras.in/agWalletAPI/v2/agg";
        Log::channel('safexpay')->info("REQUEST BODY");
        Log::channel('safexpay')->info($json);
        Log::channel('safexpay')->info("REQUEST");
        Log::channel('safexpay')->info($url);
        $api_response = $this->ApiCalls->credoPayAepsPostCall($url,$json);
        $jsno1 = json_decode($api_response);
        Log::channel('safexpay')->info("RESPONSE");
        Log::channel('safexpay')->info($api_response);
        Log::channel('safexpay')->info($jsno1->payload);
        $dec = $this->decryptData("xVHYj9hwm9J9f6AohY5Wc6TKNVhYnoLTSRoUM8/xqRQ=",$jsno1->payload);
        Log::channel('safexpay')->info("RESPONSE DEC");
        Log::channel('safexpay')->info($dec);
        // print_r($dec);
        return $dec;
    }
}
