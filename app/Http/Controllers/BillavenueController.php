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

class BillavenueController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, WalletCalculation $WalletCalculation){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->WalletCalculation = $WalletCalculation;
        
    }
    
    public function encrypt($plainText) {
        $key = $this->hextobin(md5(env('BILLAVENUE_KEY')));
        // $key = $this->hextobin(md5('9566067B7F5FA7DA4A6B8E1279395710'));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    
    public function decrypt($encryptedText) {
        $key = $this->hextobin(md5(env('BILLAVENUE_KEY')));
        // $key = $this->hextobin(md5('9566067B7F5FA7DA4A6B8E1279395710'));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
    
    public function hextobin($hexString) {
        // First decode any HTML entities
        $hexString = html_entity_decode($hexString, ENT_QUOTES, 'UTF-8');
        
        // Remove any non-hex characters
        $hexString = preg_replace('/[^0-9A-Fa-f]/', '', $hexString);
        
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            
            // Ensure we have two characters for pack
            if (strlen($subString) < 2) {
                $subString = str_pad($subString, 2, '0');
            }
            
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
    
    public function xmlToJson($xmlData){
        // Load XML into SimpleXMLElement object
        $xml = simplexml_load_string($xmlData);
        
        if ($xml === false) {
            echo "Failed to parse XML\n";
            foreach (libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }
            exit;
        }
        
        // Convert XML to JSON
        $json = json_encode($xml);
        
        if ($json === false) {
            echo "Failed to convert XML to JSON\n";
            exit;
        }
        
        return $json;
        
    }
    
    public function DmtBankList()
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>BankList</requestType>
<txnType>IMPS</txnType>
</dmtServiceRequest>';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        
        
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCall($url);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        $data = json_decode($json);
        foreach($data->bankList->bankInfoArray as $r){
            $bank = banks::where('ifsc','LIKE',$r->bankCode.'%')->first();
            if($bank){
                // echo $r->bankName;
                // echo " = ";
                // echo $r->bankCode;
                // echo " = ";
                // echo $bank->name;
                // echo " = ";
                // echo $bank->ifsc;
                // echo "<br>";
                // $bank->bill_id = $r->bankCode;
                // $bank->save();
            }else{
                echo $r->bankName;
                echo " = ";
                echo $r->bankCode;
                echo "<br>";
            }
            
        } 
        
        //return $json;
    }
    
    public function DmtCustomerLogin($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>SenderDetails</requestType>
<senderMobileNumber>'.$data['mobile'].'</senderMobileNumber>
<txnType>IMPS</txnType>
<bankId>'.$data['bank_id'].'</bankId>
</dmtServiceRequest>';
        
        $enc = $this->encrypt($xml);
        
        logintxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto logintxnid;
        }
        
        $poat = array("encRequest"=>$enc);
        $poat = $enc;
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        Log::channel('billavenue')->info($poat);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtCreateCustomer($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>SenderRegister</requestType>
<senderMobileNumber>'.$data["mobile"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<senderName>'.$data["name"].'</senderName>
<senderPin>'.$data["pincode"].'</senderPin>
<bankId>'.$data['bank_id'].'</bankId>
<aadharNumber>'.$data["aadhar"].'</aadharNumber>
<bioPid>'.$data["piddata"].'</bioPid>
<bioType>'.$data["biotype"].'</bioType>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        Createtxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto Createtxnid;
        }
        $poat = array("encRequest"=>$enc);
        $poat = $enc;
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtOtpCustomerVerify($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>VerifySender</requestType>
<senderMobileNumber>'.$data["mobile"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<otp>'.$data["otp"].'</otp>
<additionalRegData>'.$data["additional_reg_data"].'</additionalRegData>
<aadharNumber>'.$data["aadhar"].'</aadharNumber>
<bioPid>'.$data["piddata"].'</bioPid>
<bioType>'.$data["biotype"].'</bioType>
<bankId>'.$data['bank_id'].'</bankId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        OtpCustomertxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto OtpCustomertxnid;
        }
        
        $poat = array("encRequest"=>$enc);
        $poat = $enc;
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtGetBeneficiaries($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>AllRecipient</requestType>
<senderMobileNumber>'.$data['mobile'].'</senderMobileNumber>
<txnType>IMPS</txnType>
<bankId>'.$data['bank_id'].'</bankId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        GetBeneficiariestxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto GetBeneficiariestxnid;
        }
        $poat = array("encRequest"=>$enc);
        $poat = $enc;
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtGetBeneficiary($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>GetRecipient</requestType>
<senderMobileNumber>'.$data["customer_id"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<recipientId>'.$data["recipient_id"].'</recipientId>
<bankId>'.$data['bank_id'].'</bankId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        GetBeneficiarytxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto GetBeneficiarytxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtDeleteBeneficiary($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>DelRecipient</requestType>
<senderMobileNumber>'.$data["mobile"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<recipientId>'.$data["beneficiary_id"].'</recipientId>
<bankId>'.$data['bank_id'].'</bankId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        DeleteBeneficiarytxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto DeleteBeneficiarytxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtAddBeneficiary($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>RegRecipient</requestType>
<senderMobileNumber>'.$data['mobile_number'].'</senderMobileNumber>
<txnType>IMPS</txnType>
<recipientName>'.$data['recipient_name'].'</recipientName>
<recipientMobileNumber>'.$data['recipient_mobile'].'</recipientMobileNumber>
<bankCode>'.$data['bank_id'].'</bankCode>
<bankAccountNumber>'.$data['account'].'</bankAccountNumber>
<ifsc>'.$data['ifsc'].'</ifsc>
<bankId>'.$data['bank_channel'].'</bankId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        AddBeneficiarytxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto AddBeneficiarytxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtServiceReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtGetCCFFee($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<requestType>GetCCFFee</requestType>
<agentId>CC01BP67AGTU00000001</agentId>
<txnAmount>'.$data['amount'].'</txnAmount>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        GetCCFFeetxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto GetCCFFeetxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtdoTransactions($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<requestType>TXNSENDOTP</requestType>
<senderMobileNo>'.$data['customer_id'].'</senderMobileNo>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<recipientId>'.$data['recipient_id'].'</recipientId>
<txnAmount>'.$data['amount'].'</txnAmount>
<convFee>'.$data['con_fee'].'</convFee>
<txnType>'.$data['transfer_type'].'</txnType>
<bankId>'.$data['bank_id'].'</bankId>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        doTransactionstxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto doTransactionstxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function DmtdoTransactionsOtpVerify($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<requestType>TXNVERIFYOTP</requestType>
<senderMobileNo>'.$data['customer_id'].'</senderMobileNo>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<recipientId>'.$data['recipient_id'].'</recipientId>
<txnAmount>'.$data['amount'].'</txnAmount>
<convFee>'.$data['con_fee'].'</convFee>
<txnType>'.$data['transfer_type'].'</txnType>
<otp>'.$data['otp'].'</otp>
<bankId>'.$data['bank_id'].'</bankId>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        doTransactionstxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto doTransactionstxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        // $url = 'https://stgapi.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode=AVJJ02UO18AC34PGQY&requestId='.$requestId.'&ver=1.1&instituteId=PP98';
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function checkStatus($data){
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<requestType>MultiTxnStatus</requestType>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<uniqueRefId>'.$data["uniqueRefId"].'</uniqueRefId>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        checkStatustxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto checkStatustxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function refundRequest($data){
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<requestType>TxnRefund</requestType>
<txnId>'.$data["txnId"].'</txnId>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        refundRequesttxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto refundRequesttxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
    
    public function refundOtpVerify($data){
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<requestType>VerifyRefundOtp</requestType>
<txnId>'.$data["txnId"].'</txnId>
<uniqueRefId>'.$data["uniqueRefId"].'</uniqueRefId>
<otp>'.$data["otp"].'</otp>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        refundOtpVerifytxnid:
        $string = $this->UserAuth->txnIdBa('BA');
        $requestId = $string.$string."0";
        $length = strlen($requestId);
        if($length != 35){
            goto refundOtpVerifytxnid;
        }
        
        
        // $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        $poat = $enc;
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID");
        
        Log::channel('billavenue')->info("REQUEST BODY");
        Log::channel('billavenue')->info($xml);
        Log::channel('billavenue')->info("REQUEST");
        Log::channel('billavenue')->info($url);
        $api_response = $this->ApiCalls->billAvenuePostCallWithParam($url,$poat);
        
        Log::channel('billavenue')->info("RESPONSE");
        Log::channel('billavenue')->info($api_response);
        $dec = $this->decrypt($api_response);
        Log::channel('billavenue')->info("RESPONSE DEC");
        Log::channel('billavenue')->info($dec);
        
        $json = $this->xmlToJson($dec);
        
        return $json;
    }
}
