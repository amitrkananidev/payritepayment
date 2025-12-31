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

class CredoPayController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, WalletCalculation $WalletCalculation){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->WalletCalculation = $WalletCalculation;
        
    }
    
    public function encryptNew($data) {
        $key = "187c8acfc77c4369af8d16cba5db79ba";
        $key = substr(hash('sha256', $key, true), 0, 16); // Ensure key is 16 bytes
        $encrypted = openssl_encrypt($data, 'aes-256-ecb', $key, OPENSSL_RAW_DATA);
        return base64_encode($encrypted);
    }
    
    public function encrypt($plainText) {
        $key = $this->hextobin(md5("187c8acfc77c4369af8d16cba5db79ba"));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    
    public function decrypt($encryptedText) {
        $key = $this->hextobin(md5("187c8acfc77c4369af8d16cba5db79ba"));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
    
    public function hextobin($hexString) {
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            //echo $subString;exit;           
            $packedString = pack("H*", $subString);
            if ($count == 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }
    
            $count += 2;
        }
        return $binString;
    }
    
    function getHashedAadhaar($aadhaar) {
        try {
            // Compute the MD5 hash of the given Aadhaar
            $messageDigest = md5($aadhaar, true);
            
            // Convert the binary hash to a big integer
            $bigInteger = gmp_init(bin2hex($messageDigest), 16);
            
            // Convert the big integer to a hexadecimal string
            $hashText = gmp_strval($bigInteger, 16);
            
            // Pad with leading zeros if necessary to ensure the length is 32
            while (strlen($hashText) < 32) {
                $hashText = "0" . $hashText;
            }
    
            return $hashText;
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
    
    function encryptAadhar($key, $text) {
        // Ensure the key is 32 bytes long (256 bits)
        $key = substr($key, 0, 32);
        
        // Encrypt the text using AES-256-ECB mode with PKCS7 padding
        $encryptedText = openssl_encrypt($text, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
        
        // Apply PKCS7 padding manually
        $blockSize = 16;
        $pad = $blockSize - (strlen($text) % $blockSize);
        $text = $text;
        
        // Encrypt the padded text again
        $encryptedText = openssl_encrypt($text, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
        
        // Convert to base64 to match the C# output
        return base64_encode($encryptedText);
    }
    
    function decryptAadhar($key, $encryptedText) {
        // Ensure the key is 32 bytes long (256 bits)
        $key = substr($key, 0, 32);
        
        // Decode the base64 encoded encrypted text
        $encryptedText = base64_decode($encryptedText);
    
        // Decrypt the text using AES-256-ECB mode with PKCS7 padding
        $decryptedText = openssl_decrypt($encryptedText, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
        return $decryptedText;
        // Remove PKCS7 padding manually
        $blockSize = 16;
        $pad = ord($decryptedText[strlen($decryptedText) - 1]);
        
        if ($pad > 0 && $pad <= $blockSize) {
            $decryptedText = substr($decryptedText, 0, -$pad);
        }
    
        return $decryptedText;
    }
    
    function maskAadhaarNumber($aadhaar) {
        // Replace the middle 4 digits with 'XXXX'
        return substr($aadhaar, 0, 6) . 'XXXX' . substr($aadhaar, -2);
    }
    
    public function merchantOnbording($data)
    {   
        $roundedLatitude = round($data["latitude"], 7);
        $roundedlongitude = round($data["longitude"], 7);
        $json = '{
                     "salesInformation": {
                     "merchantReferenceNumber": "'.$data["merchantReferenceNumber"].'"
                     },
                     "companyInformation": {
                     "legalName": "'.$data["firstName"].' '.$data["lastName"].'",
                     "brandName": "'.$data["legalName"].'",
                     "address": "'.$data["address"].'",
                     "pincode": "'.$data["pincode"].'",
                     "latitude": "'.$roundedLatitude.'",
                     "longitude": "'.$roundedlongitude.'",
                     "businessType": "INDIVIDUAL",
                     "establishedYear": "'.$data["establishedYear"].'",
                     "mcc": "5311",
                     "merchantClassification": "Small",
                     "statementFrequency": "daily",
                     "statementType": "excel",
                     "emailDomain": ["gmail.com"]
                     },
                    "channel": [
                         {
                         "name": "AEPS",
                         "transactionSets": [
                                "Mini Statement",
                                "Withdrawal",
                                "Balance Enquiry",
                                "Sales"
                            ]
                         },
                         {
                         "name": "UPI",
                         "transactionSets": [
                                "Sales"
                            ]
                         }
                     ],
                     "personalInfo": [
                        {
                        "pan": {
                            "documentNumber": "'.$data["pan_NO"].'",
                            "url": ["'.$data["pan_URL"].'"]
                            },
                        "aadhar": {
                            "documentNumber": "'.$data["aadhar_NO"].'",
                            "url": ["'.$data["aadhar_URL_1"].'","'.$data["aadhar_URL_2"].'"]
                            },
                        "title": "'.$data["title"].'",
                        "dob": "'.$data["dob"].'",
                        "firstName": "'.$data["firstName"].'",
                        "lastName": "'.$data["lastName"].'",
                        "address": "'.$data["personal_address"].'",
                        "pincode": "'.$data["personal_pincode"].'",
                        "mobile": "'.$data["mobile"].'",
                        "email": "'.$data["email"].'",
                        "nationality": "Indian"
                        }
                    ],
                     "settlementInformation": [
                     {
                     "bankAccountNumber": "'.$data["bankAccountNumber"].'",
                     "ifsc": "'.$data["ifsc"].'",
                     "accountType": "'.$data["accountType"].'"
                     }
                     ],
                     "riskProfileName": "Payrite Plan",
                     "otherDocuments": [
                     {
                     "name": "CANCELLED_CHEQUE",
                     "documentNumber": "'.$data["CANCELLED_CHEQUE_NO"].'",
                     "url": ["'.$data["CANCELLED_CHEQUE_URL"].'"]
                     }
                     ],
                     "terminalInfo": [
                     {
                     "terminalName": "'.$data["firstName"].'",
                     "address": "'.$data["address"].'",
                     "pincode": "'.$data["pincode"].'",
                     "channel": ["AEPS","UPI"],
                     "deviceModel": "VM30",
                     "deviceSerialNumber": "'.$data["deviceSerialNumber"].'",
                     "deviceImeiNumber": "'.$data["mobile"].'",
                     "upiTerminalType": "OFFLINE",
                     "upiOfflineType": "BOTH"
                     }
                     ],
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/merchant";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function merchantUpdatingService($data,$id)
    {
        
        $json = '{
                    "companyInformation": {
                     "legalName": "'.$data["firstName"].' '.$data["lastName"].'",
                     "brandName": "'.$data["legalName"].'",
                     "address": "'.$data["address"].'",
                     "pincode": "'.$data["pincode"].'",
                     "businessType": "INDIVIDUAL",
                     "establishedYear": "'.$data["establishedYear"].'",
                     "mcc": "5311",
                     "merchantClassification": "Small",
                     "statementFrequency": "daily",
                     "statementType": "excel",
                     "emailDomain": ["gmail.com"]
                     },
                    "channel": [
                         {
                         "name": "AEPS",
                         "transactionSets": [
                                "Mini Statement",
                                "Withdrawal",
                                "Balance Enquiry",
                                "Sales"
                            ]
                         },
                         {
                         "name": "UPI",
                         "transactionSets": [
                                "Sales"
                            ]
                         }
                     ],
                     "personalInfo": [
                        {
                        "pan": {
                            "documentNumber": "'.$data["pan_NO"].'",
                            "url": ["'.$data["pan_URL"].'"]
                            },
                        "aadhar": {
                            "documentNumber": "'.$data["aadhar_NO"].'",
                            "url": ["'.$data["aadhar_URL_1"].'","'.$data["aadhar_URL_2"].'"]
                            },
                        "title": "'.$data["title"].'",
                        "dob": "'.$data["dob"].'",
                        "firstName": "'.$data["firstName"].'",
                        "lastName": "'.$data["lastName"].'",
                        "address": "'.$data["personal_address"].'",
                        "pincode": "'.$data["personal_pincode"].'",
                        "mobile": "'.$data["mobile"].'",
                        "email": "'.$data["email"].'",
                        "nationality": "Indian"
                        }
                    ],
                    "settlementFlag": "Direct",
                    "settlementInformation": [
                     {
                     "bankAccountNumber": "'.$data["bankAccountNumber"].'",
                     "ifsc": "'.$data["ifsc"].'",
                     "accountType": "'.$data["accountType"].'"
                     }
                     ],
                     "riskProfileName": "Payrite Plan",
                     "otherDocuments": [
                     {
                     "name": "CANCELLED_CHEQUE",
                     "documentNumber": "'.$data["CANCELLED_CHEQUE_NO"].'",
                     "url": ["'.$data["CANCELLED_CHEQUE_URL"].'"]
                     }
                     ],
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/merchant-update/$id";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPutCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
        
    }
    
    public function merchantUpdating($data,$id)
    {   
        $roundedLatitude = round($data["latitude"], 7);
        $roundedlongitude = round($data["longitude"], 7);
        $json = '{
                     "companyInformation": {
                     "legalName": "'.$data["firstName"].' '.$data["lastName"].'",
                     "brandName": "'.$data["legalName"].'",
                     "address": "'.$data["address"].'",
                     "pincode": "'.$data["pincode"].'",
                     "latitude": "'.$roundedLatitude.'",
                     "longitude": "'.$roundedlongitude.'",
                     "businessType": "INDIVIDUAL",
                     "establishedYear": "'.$data["establishedYear"].'",
                     "mcc": "5311",
                     "merchantClassification": "Small",
                     "statementFrequency": "daily",
                     "statementType": "excel",
                     "emailDomain": ["gmail.com"]
                     },
                    "channel": [
                         {
                         "name": "AEPS",
                         "transactionSets": [
                                "Mini Statement",
                                "Withdrawal",
                                "Balance Enquiry",
                                "Sales"
                            ]
                         },
                         {
                         "name": "UPI",
                         "transactionSets": [
                                "Sales"
                            ]
                         }
                     ],
                     "personalInfo": [
                        {
                        "pan": {
                            "documentNumber": "'.$data["pan_NO"].'",
                            "url": ["'.$data["pan_URL"].'"]
                            },
                        "aadhar": {
                            "documentNumber": "'.$data["aadhar_NO"].'",
                            "url": ["'.$data["aadhar_URL_1"].'","'.$data["aadhar_URL_2"].'"]
                            },
                        "title": "'.$data["title"].'",
                        "dob": "'.$data["dob"].'",
                        "firstName": "'.$data["firstName"].'",
                        "lastName": "'.$data["lastName"].'",
                        "address": "'.$data["personal_address"].'",
                        "pincode": "'.$data["personal_pincode"].'",
                        "mobile": "'.$data["mobile"].'",
                        "email": "'.$data["email"].'",
                        "nationality": "Indian"
                        }
                    ],
                     "settlementInformation": [
                     {
                     "bankAccountNumber": "'.$data["bankAccountNumber"].'",
                     "ifsc": "'.$data["ifsc"].'",
                     "accountType": "'.$data["accountType"].'"
                     }
                     ],
                     "riskProfileName": "Payrite Plan",
                     "otherDocuments": [
                     {
                     "name": "CANCELLED_CHEQUE",
                     "documentNumber": "'.$data["CANCELLED_CHEQUE_NO"].'",
                     "url": ["'.$data["CANCELLED_CHEQUE_URL"].'"]
                     }
                     ],
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/merchant-update/$id";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPutCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function terminalUpdating($data,$id)
    {   
        $json = '{
                    "terminalName": "'.$data["firstName"].'",
                     "address": "'.$data["address"].'",
                     "pincode": "'.$data["pincode"].'",
                     "channel": ["AEPS","UPI"],
                     "deviceModel": "VM30",
                     "deviceSerialNumber": "'.$data["deviceSerialNumber"].'",
                     "deviceImeiNumber": "'.$data["mobile"].'",
                     "upiTerminalType": "OFFLINE",
                     "upiOfflineType": "BOTH",
                     "deviceType":"sticker",
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal-update/$id";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPutCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function terminalActivation($CPMS)
    {
        $json = '{
    "reason": "terminal activation"
}';
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal-activate/$CPMS";
        // $url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal-deactivate/$CPMS";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function terminalDeActivation($CPMS)
    {
        $json = '{
    "reason": "terminal activation"
}';
        //$url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal-activate/$CPMS";
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal-deactivate/$CPMS";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function terminalOnboarding($data)
    {   
        $json = '[{
                     "merchantCpid":"'.$data["cpid"].'",
                     "channel": ["AEPS","UPI"],
                     "deviceModel": "VM30",
                     "deviceSerialNumber": "'.$data["deviceSerialNumber"].'",
                     "deviceImeiNumber": "'.$data["mobile"].'",
                     "terminalName":"'.$data["firstName"].'",
                     "latitude": "'.$data["latitude"].'",
                     "longitude": "'.$data["longitude"].'",
                     "pincode": "'.$data["pincode"].'",
                     "address": "'.$data["address"].'",
                     "upiTerminalType": "OFFLINE",
                     "upiOfflineType": "BOTH",
                     "deviceType":"sticker",
                     "action": "SUBMIT"
                    }]';
        
        
        $url = "https://api.thecenterfirst.com/mms/v1/onboarding/terminal";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function checkFa($data)
    {   
        $json = '{
                "cpId":"'.$data["cpid"].'"
                }';
        
        
        $url = "https://api.thecenterfirst.com/transactions/v3.0/check/2fa";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayAepsPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function faAuth($data)
    {   
        $aadhar = $data['aadhaar_number'];
        $aachard_enc = $this->encryptAadhar("d8a62377de0b410d9e3ec4033bf53165",$aadhar);
        $Hashed = md5($aadhar);
        $masked = $this->maskAadhaarNumber($aadhar);
        $pdata = $data['PidData'];
        $cpid = $data['cpid'];
        $srno = $data['srno'];
        $rdsVer = $data['rdsVer'];
        $rdsId = $data['rdsId'];
        $mi = $data['mi'];
        
        $json = '{
                    "app_version":"3.0.9",
                    "hashed_aadhar":"'.$Hashed.'",
                    "aadhar":"'.$aachard_enc.'",
                    "pid_data":"'.$data["PidData"].'",
                    "masked_aadhar":"'.$masked.'",
                    "cpId":"'.$data["cpid"].'",
                    "transaction_group":"aeps",
                    "transaction_type":"Authentication",
                    "payment_method":"biometric",
                    "virtual_ID":"",
                    "biometric_srno":"'.$data["srno"].'",
                    "biometric_rdsVer":"'.$data["rdsVer"].'",
                    "biometric_rdsId":"'.$data["rdsId"].'",
                    "biometric_mi":"'.$data["mi"].'",
                    "operating_system":"Linux",
                    "transaction_origin_ip":"216.10.250.244",
                    "latitude":"22.3039",
                    "longitude":"70.8022"
                    }';
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($json);
        
        $enc = $this->encryptAadhar("063038915556437da683226ad843e124",$json);
        Log::channel('credopay')->info($enc);
        $json = '{
                    "encrypted":"'.$enc.'"
                }';
        
        
        $url = "https://api.thecenterfirst.com/transactions/v3.0/aeps/authentication";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayAepsPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        // $dec = $this->decryptAadhar("063038915556437da683226ad843e124",$enc);
        // Log::channel('credopay')->info($dec);
        
        return $api_response;
    }
    
    public function transactions($data)
    {   
        $aadhar = $data['aadhaar_number'];
        $aachard_enc = $this->encryptAadhar("d8a62377de0b410d9e3ec4033bf53165",$aadhar);
        $Hashed = md5($aadhar);
        $masked = $this->maskAadhaarNumber($aadhar);
        $pdata = $data['PidData'];
        $cpid = $data['cpid'];
        $srno = $data['srno'];
        $rdsVer = $data['rdsVer'];
        $rdsId = $data['rdsId'];
        $mi = $data['mi'];
        
        $amount = $data['amount'];
        $transaction_type = $data['transaction_type'];
        $bank_name = $data['bank_name'];
        $user_id = $data['user_id'];
        $transaction_id = $data['transaction_id'];
        
        $json = '{
                    "bank_name":"'.$bank_name.'",
                    "amount": "'.$amount.'",
                    "CRN_U": "'.$transaction_id.'",
                    "custom_field1": "'.$transaction_id.'",
                    "app_version":"3.0.9",
                    "hashed_aadhar":"'.$Hashed.'",
                    "aadhar":"'.$aachard_enc.'",
                    "pid_data":"'.$data["PidData"].'",
                    "masked_aadhar":"'.$masked.'",
                    "cpId":"'.$data["cpid"].'",
                    "transaction_group":"aeps",
                    "transaction_type":"'.$transaction_type.'",
                    "payment_method":"biometric",
                    "virtual_ID":"",
                    "biometric_srno":"'.$data["srno"].'",
                    "biometric_rdsVer":"'.$data["rdsVer"].'",
                    "biometric_rdsId":"'.$data["rdsId"].'",
                    "biometric_mi":"'.$data["mi"].'",
                    "operating_system":"Linux",
                    "transaction_origin_ip":"216.10.250.244",
                    "latitude":"22.3039",
                    "longitude":"70.8022"
                    }';
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($json);
        
        $enc = $this->encryptAadhar("063038915556437da683226ad843e124",$json);
        Log::channel('credopay')->info($enc);
        $json = '{
                    "encrypted":"'.$enc.'"
                }';
        
        
        $url = "https://api.thecenterfirst.com/transactions/v3.0/aeps/send";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayAepsPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        // $dec = $this->decryptAadhar("063038915556437da683226ad843e124",$enc);
        // Log::channel('credopay')->info($dec);
        
        return $api_response;
    }
    
    public function transactionsComplete($data)
    {
        $json = '{
                 "transaction_id":"'.$data.'"
                }';
        
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($json);
        
        // $enc = $this->encryptAadhar("063038915556437da683226ad843e124",$json);
        // Log::channel('credopay')->info($enc);
        // $json = '{
        //             "encrypted":"'.$enc.'"
        //         }';
        
        
        $url = "https://api.thecenterfirst.com/transactions/v3.0/aeps/complete";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayAepsPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        // $dec = $this->decryptAadhar("063038915556437da683226ad843e124",$enc);
        // Log::channel('credopay')->info($dec);
        
        return $api_response;
    }
    
    public function transactionsUpi($data)
    {
        
        $json = '{
"CRN_U": "'.$data["transaction_id"].'",
"custom_field1": "'.$data["user_id"].'",
"amount": "'.$data["amount"].'",
"cpId":"'.$data["cpid"].'",
"latitude":"13.024670",
"longitude":"80.207298"
}';
        
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($json);
        
        $enc = $this->encryptAadhar("566f447f36ab41a1ab3169995c4ff5c6",$json);
        Log::channel('credopay')->info($enc);
        $json = '{
                    "encrypted":"'.$enc.'"
                }';
        
        
        $url = "https://api.thecenterfirst.com/transactions/v3.0/api/upi/dynamic";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayUpiPostCall($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        // $dec = $this->decryptAadhar("063038915556437da683226ad843e124",$enc);
        // Log::channel('credopay')->info($dec);
        
        return $api_response;
    }
    
    public function merchantUpdatingUAT($data,$id)
    {   
        // $roundedLatitude = round($data["latitude"], 7);
        // $roundedlongitude = round($data["longitude"], 7);
        $json = '{
                     
                    "channel": [
                         {
                         "name": "AEPS",
                         "transactionSets": [
                                "Mini Statement",
                                "Withdrawal",
                                "Balance Enquiry",
                                "Sales"
                            ]
                         },
                         {
                         "name": "UPI",
                         "transactionSets": [
                                "Sales"
                            ]
                         }
                     ],
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://ucpbsapi.credopay.info/mms/v1/onboarding/merchant-update/CPMS29010019956";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPutCallUAT($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
    
    public function terminalUpdatingUAT($data,$id)
    {   
        // $roundedLatitude = round($data["latitude"], 7);
        // $roundedlongitude = round($data["longitude"], 7);
        $json = '{
                    "channel": ["AEPS","UPI"],
                    "pincode": "360005",
                    "deviceType": "sticker",
                    "upiTerminalType": "OFFLINE",
                    "upiOfflineType": "BOTH",
                     "action": "SUBMIT"
                    }';
        
        
        $url = "https://ucpbsapi.credopay.info/mms/v1/onboarding/terminal-update/CPST29010021027";
        
        Log::channel('credopay')->info("REQUEST BODY");
        Log::channel('credopay')->info($json);
        Log::channel('credopay')->info("REQUEST");
        Log::channel('credopay')->info($url);
        $api_response = $this->ApiCalls->credoPayPutCallUAT($url,$json);
        Log::channel('credopay')->info("RESPONSE");
        Log::channel('credopay')->info($api_response);
        
        return $api_response;
    }
}
