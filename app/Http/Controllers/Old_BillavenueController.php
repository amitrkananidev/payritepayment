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
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    
    public function decrypt($encryptedText) {
        $key = $this->hextobin(md5(env('BILLAVENUE_KEY')));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
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
        $string = $this->UserAuth->txnId('BA');
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
</dmtServiceRequest>';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
        
        return $json;
    }
    
    public function DmtGetBeneficiaries($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>AllRecipient</requestType>
<senderMobileNumber>'.$data['mobile'].'</senderMobileNumber>
<txnType>IMPS</txnType>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
        
        return $json;
    }
    
    public function DmtGetBeneficiary($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>GetRecipient</requestType>
<senderMobileNumber>'.$data["customer_id"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<recipientId>'.$data["recipient_id"].'</recipientId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
        
        return $json;
    }
    
    public function DmtDeleteBeneficiary($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtServiceRequest>
<requestType>DelRecipient</requestType>
<senderMobileNumber>'.$data["mobile"].'</senderMobileNumber>
<txnType>IMPS</txnType>
<recipientId>'.$data["beneficiary_id"].'</recipientId>
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
</dmtServiceRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
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
        $string = $this->UserAuth->txnId('BA');
        $requestId = $string.$string."0";
        
        
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        
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
        
        return $json;
    }
    
    public function DmtdoTransactions($data)
    {   
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><dmtTransactionRequest>
<requestType>FundTransfer</requestType>
<senderMobileNo>'.$data['customer_id'].'</senderMobileNo>
<agentId>CC01BP67AGTU00000001</agentId>
<initChannel>AGT</initChannel>
<recipientId>'.$data['recipient_id'].'</recipientId>
<txnAmount>'.$data['amount'].'</txnAmount>
<convFee>'.$data['con_fee'].'</convFee>
<txnType>'.$data['transfer_type'].'</txnType>
</dmtTransactionRequest>
';
        $enc = $this->encrypt($xml);
        $string = $this->UserAuth->txnId('BA');
        $requestId = $string.$string."0";
        
        
        $url = 'https://api.billavenue.com/billpay/dmt/dmtTransactionReq/xml?accessCode='.env("BILLAVENUE_ACCESS").'&requestId='.$requestId.'&ver=1.1&instituteId='.env("BILLAVENUE_ID").'&encRequest='.$enc;
        
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
        
        return $json;
    }
}
