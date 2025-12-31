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

class EkoController extends Controller
{
    public function __construct(UserAuth $Auth, ApiCalls $ApiCalls, WalletCalculation $WalletCalculation){
        $this->UserAuth = $Auth;
        $this->ApiCalls = $ApiCalls;
        $this->WalletCalculation = $WalletCalculation;
        
    }
    
    public function ekoOnboard(Request $request) {
        
        $user_mobile = $request->get('user_mobile');
        $usertoken = $request->get("token");
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;
        if($response != 1) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('mobile',$user_mobile)->first();
            $user_id = $user->id;
            $kyc = kyc_docs::where('user_id',$user_id)->where('status',1)->first();
            $shop = shop_details::where('user_id',$user_id)->first();  
            $address = Addresses::where('user_id',$user_id)->first();  
            
            $profile_detail = $this->UserAuth->getUserProfile($user_id);
            
            if(!$address){
                return response()->json(['success' => false,'message' => 'Please enter address!']);
            }
            
            if($kyc){
                $pan_number = $kyc->pan_number;
            }else{
                return response()->json(['success' => false,'message' => 'Verify your kyc first!']);
            }
            
            if($shop){
                $shop_name = $shop->shop_name;
            }else{
                return response()->json(['success' => false,'message' => 'Add your shop detail!']);
            }
            
            $mobile = $user->mobile;
            
            $first_name = $profile_detail->name;
            $surname = $profile_detail->surname;
            $last_name = '';
            $email = $profile_detail->email;
            $residence_address = $address->address;
            $residence_city = $profile_detail->city_name;
            $residence_state = $profile_detail->state_name; 
            $residence_pincode = $address->pincode;
            $dob = $profile_detail->dob;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."onboard";
        
            $address_data = ['line' => $residence_address, 
                'city' => $residence_city, 
                'state' => $residence_state, 
                'pincode' => $residence_pincode, 
                'district' => $residence_city,
                'area' => $residence_city,
            ];
    
            $params = [
                'initiator_id' => $initiator_id,
                'pan_number' => $pan_number,
                'mobile' => $mobile,
                'first_name' => $first_name,
                'last_name' => $surname,
                'email' => $email,
                'residence_address' => json_encode($address_data),
                'dob' => $dob,
                'shop_name' => $shop_name,
            ];
    
            $api_params = http_build_query($params, '', '&');
            Log::channel('ekoapi')->info("REQUEST");
            Log::channel('ekoapi')->info($api_url);
            Log::channel('ekoapi')->info($api_params);
            $api_response = $this->ApiCalls->ekoPutCall($api_url,$api_params);
            $api_data = json_decode($api_response);
            Log::channel('ekoapi')->info("RESPONSE");
            Log::channel('ekoapi')->info($api_response);
            
            // $reponse_table = new ResponseTable();
            // $reponse_table->response = $api_response;
            // $reponse_table->api_name = 'EKO_ONBOARDING';
            // $reponse_table->request = json_encode($params);
            // $reponse_table->save();
            
            if($api_data->status == 0) {
                
                if($api_data->response_type_id == 1290) { 
                    
                    $eko_update = User::find($user_id);
                    $eko_service = eko_services::where('user_id',$user_id)->first();
                    if($eko_service){
                        
                        $eko_service->eko_code = $api_data->data->user_code;
                        $eko_service->eko_status = 4;
                        $eko_service->save();
                    }else{
                        $eko_service = new eko_services();
                        $eko_service->user_id = $user_id;
                        $eko_service->eko_code = $api_data->data->user_code;
                        $eko_service->eko_status = 4;
                        $eko_service->save();
                    }
                    
                    
                    return response()->json(['success' => true, 'message' => $api_data->message, 'user_code' => $api_data->data->user_code, 'data' => $api_data]); 
                    
                }elseif($api_data->response_type_id == 1307){
                    $eko_update = User::find($user_id);
                    $eko_service = eko_services::where('user_id',$user_id)->first();
                    if($eko_service){
                        
                        $eko_service->eko_code = $api_data->data->user_code;
                        $eko_service->eko_status = 4;
                        $eko_service->save();
                    }else{
                        $eko_service = new eko_services();
                        $eko_service->user_id = $user_id;
                        $eko_service->eko_code = $api_data->data->user_code;
                        $eko_service->eko_status = 4;
                        $eko_service->save();
                    }
                    return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
                }else {
                    return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
                }
            }
            else 
            {
                return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
            }
        }
    }
    
    public function aepsActivateService(Request $request) {
        $user_mobile = $request->get('user_mobile');
        $usertoken = $request->get("token");
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('mobile',$user_mobile)->first();
            $user_id = $user->id;
            $eko_service = eko_services::where('user_id',$user_id)->first();
            if(!$eko_service){
                return response()->json(['success' => false,'message' => 'User Not Onboard.']);
            }
            
            $profile_detail = $this->UserAuth->getUserProfile($user_id);
            $address = Addresses::where('user_id',$user_id)->first(); 
            
            $service_code = 45;
            $user_code = $eko_service->eko_code;
            $shop = shop_details::where('user_id',$user_id)->first();
            if(!$shop){
                return response()->json(['success' => false,'message' => 'Please Upload Shop Detail']);
            }
            $office_address = $shop->office_address;
            $office_city = $profile_detail->city_name;
            $office_state = $profile_detail->state_name;
            $office_pincode = $address->pincode;
            
            $residence_address = $address->address;
            $residence_city =  $profile_detail->city_name;
            $residence_state =  $profile_detail->state_name;
            $residence_pincode = $address->pincode;
            
            $devicenumber = $request->get('devicenumber');
            $devicename = $request->get('devicename');
            
            $kyc_pan = kyc_docs::where('user_id',$user_id)->where('status',1)->first();
            
            if($kyc_pan){
                $image_name_pan = $kyc_pan->pan_image;
                $image_name_aadhar = $kyc_pan->aadhaar_front_image;
                $image_name_aadhar_b = $kyc_pan->aadhaar_back_image;
            }else{
                return response()->json(['success' => false,'message' => 'Verify your kyc first!']);
            }
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."service/activate";
        
            $office_data = ['line' => $office_address, 
                'city' => $office_city, 
                'state' => $office_state, 
                'pincode' => $office_pincode, 
            ];
            
            $residence_data = ['line' => $residence_address, 
                'city' => $residence_city, 
                'state' => $residence_state, 
                'pincode' => $residence_pincode,
            ];
    
            $params = [
                'service_code' => $service_code,
                'initiator_id' => $initiator_id,
                'user_code' => $user_code,
                'devicenumber' => $devicenumber,
                'modelname' => $devicename,
                //   'office_address' => rawurlencode($office_data),
            // 'address_as_per_proof' => rawurlencode($residence_data),
            ];
            
            
            // 'office_address' => json_encode($office_data),
            // 'address_as_per_proof' => json_encode($residence_data),
                
            $api_params = http_build_query($params, '', '&');
            
            // $api_params = http_build_query($params, '', '&');
            
            $string = $api_params.'&office_address='.json_encode($office_data).'&address_as_per_proof='.json_encode($residence_data);
            
            // exit;
                // $api_params = urlencode($params);
            
            $cfile1 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_pan));
            $cfile2 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_aadhar));
            $cfile3 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_aadhar_b));
        
            $post = array(
            'pan_card' => $cfile1,
            'aadhar_front' => $cfile2,
			'aadhar_back' => $cfile3,
			'form-data' => $string ,
			); 
           
            Log::channel('ekoapi')->info("REQUEST");
            Log::channel('ekoapi')->info($post);
            // print_r($api_url);
            $api_response = $this->ApiCalls->ekoPutCall($api_url,$post);
            $api_data = json_decode($api_response);
            Log::channel('ekoapi')->info("RESPONSE");
            Log::channel('ekoapi')->info($api_response);
            
            // $reponse_table = new ResponseTable();
            // $reponse_table->response = $api_response;
            // $reponse_table->api_name = 'EKO_SERIVCESACTIVE';
            // $reponse_table->request = json_encode($post);
            // $reponse_table->save();
            
            if($api_data->status == 0) {
                
                if($api_data->response_type_id == 1259) {
                    
                    $eko_update = User::find($user_id);
                    $eko_service = eko_services::where('user_id',$user_id)->first();
                    if($eko_service){
                        $eko_service->eko_status = 1;
                        $eko_service->eko_payout = 2;//$eko_service->eko_aeps = 2;
                        $eko_service->save();
                    }
                
                    return response()->json(['success' => true, 'message' => $api_data->message, 'data' => $api_data]);   
                }
                else {
                    return response()->json(['success' => false, 'message' => $api_data->message,'data' => $api_data ]);
                }
            }elseif($api_data->status == 1295){
                $eko_update = User::find($user_id);
                $eko_service = eko_services::where('user_id',$user_id)->first();
                if($eko_service){
                    $eko_service->eko_status = 1;
                    $eko_service->eko_payout = 1;
                    // $eko_service->eko_aeps = 1;
                    $eko_service->save();
                }
                
                return response()->json(['success' => true, 'message' => $api_data->message,'data' => $api_data ]);
            }else{
                return response()->json(['success' => false, 'message' => $api_data->message,'data' => $api_data ]);
            }
                
        }
    }
    
    public function aepsUserServiceEnquiry(Request $request) {
        $user_mobile = $request->get('user_mobile');
        $usertoken = $request->get("token");
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('mobile',$user_mobile)->first();
            $user_id = $user->id;
            $user_data = User::find($user_id);
            
            $eko_service = eko_services::where('user_id',$user_id)->first();
            if(!$eko_service){
                return response()->json(['success' => false,'message' => 'User Not Onboard.']);
            }
            
            $service_code = $request->get("service_code");
            $user_code = $eko_service->eko_code;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."services/user_code:".$user_code."?initiator_id=".$initiator_id;
            
            Log::channel('ekoapi')->info("REQUEST");
            Log::channel('ekoapi')->info($api_url);
            $api_response = $this->ApiCalls->ekoGetCall($api_url);
            Log::channel('ekoapi')->info("RESPONSE");
            Log::channel('ekoapi')->info($api_response);
            // $reponse_table = new ResponseTable();
            // $reponse_table->response = $api_response;
            // $reponse_table->api_name = 'EKO_SERIVCE_ENQUIRY';
            // $reponse_table->request = $api_url;
            // $reponse_table->save();
            
            $api_data = json_decode($api_response);
            
            if($api_data->status == 0) {
                
                // $var = json_decode($api_data->data->service_status_list[0]);
                
                if(isset($api_data->data->service_status_list)) {
                    $service_status_list = $api_data->data->service_status_list;
                    foreach($service_status_list as $r){
                        // if($r->service_code == 43){
                            
                        
                        //     $verification_status = $r->verification_status; 
                        //     $status = $r->status; 
                            
                        //     if($status == 1) {
                                
                        //         $eko_update = User::find($user_id);
                        //         $eko_service = eko_services::where('user_id',$user_id)->first();
                        //         if($eko_service){
                        //             $eko_service->eko_status = 1;
                        //             $eko_service->eko_aeps = 1;
                        //             $eko_service->save();
                        //         }
                                
                        //         return response()->json(['success' => true, 'message' => 'Service activated successfully.']);
                                
                        //     }elseif($status == 0) {
                                
                        //         $eko_update = User::find($user_id);
                        //         $eko_service = eko_services::where('user_id',$user_id)->first();
                        //         if($eko_service){
                        //             $eko_service->eko_status = 4;
                        //             $eko_service->eko_aeps = 3;
                        //             $eko_service->save();
                        //         }
                                
                        //         return response()->json(['success' => false, 'message' => 'User needs to upload the documents again using activate service API']);
                        //     }
                        //     else {
                        //         return response()->json(['success' => false, 'message' => 'User status id pending for the service']);
                        //     }
                        // }
                        
                        if($r->service_code == 45){
                            
                        
                            $verification_status = $r->verification_status; 
                            $status = $r->status; 
                            
                            if($status == 1) {
                                
                                $eko_update = User::find($user_id);
                                $eko_service = eko_services::where('user_id',$user_id)->first();
                                if($eko_service){
                                    $eko_service->eko_status = 1;
                                    $eko_service->eko_payout = 1;
                                    $eko_service->save();
                                }
                                
                                return response()->json(['success' => true, 'message' => 'Service activated successfully.']);
                                
                            }elseif($status == 0) {
                                
                                $eko_update = User::find($user_id);
                                $eko_service = eko_services::where('user_id',$user_id)->first();
                                if($eko_service){
                                    $eko_service->eko_status = 4;
                                    $eko_service->eko_payout = 3;
                                    $eko_service->save();
                                }
                                
                                return response()->json(['success' => false, 'message' => 'User needs to upload the documents again using activate service API']);
                            }
                            else {
                                return response()->json(['success' => false, 'message' => 'User status id pending for the service']);
                            }
                        }
                    }
                }
            }
            else {
                return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data ]);
            }
        }
    }
    
    public function ekoUserServiceList(Request $request) {
        $user_mobile = $request->get('user_mobile');
        $usertoken = $request->get("token");
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('mobile',$user_mobile)->first();
            $user_id = $user->id;
            $user_data = User::find($user_id);
            
            $service_code = 45;
            $user_code = 198481007;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."services?initiator_id=".$initiator_id;
            
            $api_response = $this->ApiCalls->ekoGetCall($api_url);
            print_r($api_response);exit;
            // $reponse_table = new ResponseTable();
            // $reponse_table->response = $api_response;
            // $reponse_table->api_name = 'EKO_SERIVCE_ENQUIRY';
            // $reponse_table->request = $api_url;
            // $reponse_table->save();
            
            $api_data = json_decode($api_response);
            
            if($api_data->status == 0) {
                
                // $var = json_decode($api_data->data->service_status_list[0]);
                
                if(isset($api_data->data->service_status_list)) {
                
                    $verification_status = $api_data->data->service_status_list[0]->verification_status; 
                    $status = $api_data->data->service_status_list[0]->status; 
                    
                    if($status == 1) {
                        
                        $eko_update = User::find($user_id);
                        $eko_update->eko_status = 1; //success
                        $eko_update->save();
                        
                        return response()->json(['success' => true, 'message' => 'Service activated successfully.']);
                        
                    } elseif($status == 0) {
                        
                        $eko_update = User::find($user_id);
                        $eko_update->eko_status = 3; //rejected
                        $eko_update->save();
                        
                        return response()->json(['success' => false, 'message' => 'User needs to upload the documents again using activate service API']);
                    }
                    else {
                        return response()->json(['success' => false, 'message' => 'User status id pending for the service']);
                    }
                }
            }
            else {
                return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data ]);
            }
        }
    }
    
    public function payoutTransaction($data,$user_code) {
        $api_url = env('EKO_PAYOUT_URL')."user_code:".$user_code."/settlement";
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($data);
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$data);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function payoutTransactionUpi($data,$user_code) {
        $api_url = env('EKO_PAYOUT_URL')."user_code:".$user_code."/vpa-settlement";
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($data);
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$data);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function bankVerification($data,$user_code,$ifsc,$acc) {
        $api_url = "https://api.eko.in:25002/ekoicici/v2/banks/ifsc:$ifsc/accounts/$acc";
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$data);
        return $api_response;
    }
    
    public function aepsKeysData(Request $request) {
        $user_mobile = $request->get('user_mobile');
        $usertoken = $request->get("token");
        
        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('mobile',$user_mobile)->first();
            
            $environment = env('EKO_AEPS_ENVIRONMENT');
            $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $key = env('EKO_AEPS_KEY');
            
            $encodedKey = base64_encode($key);
            $secret_key_timestamp = "".round(microtime(true) * 1000);
            $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
            $secret_key = base64_encode($signature);
            $eko = eko_services::where('user_id',$user->id)->first();
            if(!$eko){
                return response()->json(['success' => false,'message' => 'Please Activate AEPS.']);
            }
            return response()->json(['success' => true,'message' => 'AEPS Keys data', 
            'secret_key' => $secret_key,
            'secret_key_timestamp' => $secret_key_timestamp,
            'environment' => $environment,
            'developer_key' => $developer_key,
            'initiator_id' => $initiator_id,
            'user_code' => $eko->eko_code,
            'logo' => env('APP_URL').'/logo.png',
            'partner_name' => env('APP_NAME'),
            'callbackurl' => env('APP_URL').'/api/v1/aeps/aeps_callback_url'
            
            ]);
        }
    }
    
    public function aepsCallbackUrl(Request $request) {
        
        // header("Access-Control-Allow-Origin: *");
        // header('Access-Control-Allow-Origin: https://gateway.eko.in');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        // ini_set("allow_url_fopen", 1);
        $body = file_get_contents('php://input');
        
        Log::channel('ekoapi')->info("CALLBACK RESPONSE");
        Log::channel('ekoapi')->info($body);
        
        // $insert_data = ResponseTable::find($reponse_table->id);
        // if(empty($insert_data->response)){
        //     return response()->json(['action' => 'go','allow' => false,'message'=>'Server Down']);
        // }
        $data = json_decode($body,true);
        
        if($data['action']) {
            
            if($data['action'] == 'debit-hook') {
            
                $type = $data['detail']['data']['type'];
                
                if($type == 3 || $type == 4) {
                    
                    $user_code = $data['detail']['data']['user_code'];
                    $customer_id = $data['detail']['data']['customer_id'];
                    $client_ref_id = $data['detail']['client_ref_id'];
                    $environment = env('EKO_AEPS_ENVIRONMENT');
                    $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
                    $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                    $key = env('EKO_AEPS_KEY');
                    $encodedKey = base64_encode($key);
                    
                    $secret_key_timestamp = "".round(microtime(true) * 1000);
                    
                    $request_signature = $secret_key_timestamp . $customer_id . $user_code;
                    
                    $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
                    $signature_req_hash = hash_hmac('SHA256', $request_signature, $encodedKey, true);
                    
                    $secret_key = base64_encode($signature);
                    $request_hash = base64_encode($signature_req_hash);
                    
                    return response()->json(['action' => 'go','allow' => true,'secret_key_timestamp' => $secret_key_timestamp,
                    'request_hash' => $request_hash, 'secret_key' =>$secret_key]);
                    
                }
                elseif($type == 2) {
                    
                    $user_code = $data['detail']['data']['user_code'];
                    $customer_id = $data['detail']['data']['customer_id'];
                    $amount = $data['detail']['data']['amount'];
                    $client_ref_id = $data['detail']['client_ref_id'];
                    $BankIIN = $data['detail']['data']['bank_code'];
                    
                    $environment = env('EKO_AEPS_ENVIRONMENT');
                    $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
                    $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                    $key = env('EKO_AEPS_KEY');
                    $encodedKey = base64_encode($key);
                    
                    $secret_key_timestamp = "".round(microtime(true) * 1000);
                    $request_signature = $secret_key_timestamp . $customer_id . $amount . $user_code;
                    
                    $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
                    $signature_req_hash = hash_hmac('SHA256', $request_signature, $encodedKey, true);
                    
                    $secret_key = base64_encode($signature);
                    $request_hash = base64_encode($signature_req_hash);
                    
                    
                    $txn_id= $this->UserAuth->txnId('AE');
                    
                    $registered = User::where('eko_user_code',$user_code)->first();
                    if($registered){
                        $user_id = $registered->id;
                        
                        //Transaction_table
                        $transactions = new transactions_aeps();
                        $transactions->user_id = $user_id;
                        $transactions->transaction_id = $txn_id;
                        $transactions->vendor_id = $customer_id;
                        $transactions->outlet_id = $user_code;
                        $transactions->bank_iin = $BankIIN;
                        $transactions->amount = $amount;
                        $transactions->event = "AEPSTXN";
                        $transactions->transfer_type = "AEPS";
                        $transactions->status = 0;
                        $transactions->referenceId  = $client_ref_id;
                        $transactions->reason = 'Request Accepted!';
                        $transactions->save();
                        
                        return response()->json(['action' => 'go','allow' => true, 'secret_key_timestamp' => $secret_key_timestamp,
                        'request_hash' => $request_hash, 'secret_key' => $secret_key ]);
                    }
                    else {
                        return response()->json(['action' => 'go','allow' => false,'message'=>'Server Down']);
                    }
                }
                else {
                    return response()->json(['action' => 'go', 'allow' => false, 'message' => 'Server Down']);
                }
            }
            elseif($data['action'] == 'eko-response'){
                
                $response_status = $data['detail']['response']['response_status_id'];
                $client_ref_id = $data['detail']['client_ref_id'];
            
                if($response_status == 0) {
                    
                    $status = $data['detail']['response']['data']['tx_status'];
                    if($status == 0) { //success
                        
                        $amount = $data['detail']['response']['data']['amount'];
                        $sender_name = $data['detail']['response']['data']['sender_name'];
                        $tid = $data['detail']['response']['data']['tid'];
                        $merchantname = $data['detail']['response']['data']['merchantname'];
                        $aadhaar = $data['detail']['response']['data']['aadhar'];
                        $bank_ref_num = $data['detail']['response']['data']['bank_ref_num'];
                    
                        $txn_find = transactions_aeps::where('referenceId',$client_ref_id)->where('status',0)->first();
                        
                        if($txn_find) {
                            $user_id = $txn_find->user_id;
                            
                            $user = User::find($user_id);
                            $balance = $userwallet->wallet->balanceFloat;
                            
                            $commission = 10;
                                $commission_type = 'flat';
                                if($commission_type == 'flat'){
                                    $commission = 10;
                                }else{
                                    $commission = $amount * $commission / 100;
                                }
                            $wallet = $userwallet->wallet;
                            
                            $wallet->withdrawFloat($amount,[
                                'meta' => [
                                    'Title' => 'AEPS',
                                    'detail' => 'AEPS : '.$txn_find->transaction_id,
                                    'transaction_id' => $txn_find->transaction_id,
                                ]
                            ]);
                            //commission
                            // if($amount > 4000) {
                            //     $commission = 9;
                            // }else{
                            //     $commission = $amount * 0.2 / 100;
                            // }
                            
                            $total_amount = $amount + $commission;
                            
                            $txn_add = transactions_aeps::find($txn_find->id);
                            $txn_add->ben_name = $sender_name;  //sender_name
                            $txn_add->tid = $tid;
                            $txn_add->status = 1;
                            $txn_add->remitterName = $merchantname;  //merchantname
                            $txn_add->aadhaar = $aadhaar;
                            $txn_add->utr = $bank_ref_num;  //rrn
                            $txn_add->commission = round($commission,5);
                            $txn_add->txn_type = 'Credit';
                            $txn_add->save();
                            
                            $transaction_id = $txn_add->transaction_id;
                            
                            $aepscomm = $this->WalletCalculation->retailerAeps($transaction_id);
                            $aepscommdist = $this->WalletCalculation->distributorAeps($transaction_id);
                        }
                        elseif($status == 1){
                            $txn_find = transactions_aeps::where('referenceId',$client_ref_id)->first();
                            if($txn_find){
                                $txn_add = Transactions::find($txn_find->id);
                                $txn_add->status = 2;
                                $txn_add->save();
                            }
                        }
                        else { }
                    }
                }
                else 
                {
                    $txn_find = transactions_aeps::where('referenceId',$client_ref_id)->first();
                    if($txn_find){
                        $txn_add = Transactions::find($txn_find->id);
                        $txn_add->status = 2;
                        $txn_add->save();
                    }
                }
                
            }
            else
            {
                return response()->json(['action' => 'go','allow' => false,'message'=>'Server is down!']);
            }
        }
        // $api_response = '';
        // return $api_response;
    }
    
    public function encryptAadhaar() {
        $rawPublicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCaFyrzeDhMaFLx+LZUNOOO14Pj9aPfr+1WOanDgDHxo9NekENYcWUftM9Y17ul2pXr3bqw0GCh4uxNoTQ5cTH4buI42LI8ibMaf7Kppq9MzdzI9/7pOffgdSn+P8J64CJAk3VrVswVgfy8lABt7fL8R6XReI9x8ewwKHhCRTwBgQIDAQAB";
    
        // Add the necessary PEM header and footer
        $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($rawPublicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        
        // Get the public key resource
        $publicKey = openssl_pkey_get_public($publicKeyPem);
        
        if ($publicKey === false) {
            throw new Exception('Invalid public key: ' . openssl_error_string());
        }
        
        return $publicKey;
    }
    
    public function aepsEkycOtp(Request $request) {
    
        $aadhaarNumber = $request->aadhar;
        $mobile = $request->mobile;
        // Public key
        $public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCaFyrzeDhMaFLx+LZUNOOO14Pj9aPfr+1WOanDgDHxo9NekENYcWUftM9Y17ul2pXr3bqw0GCh4uxNoTQ5cTH4buI42LI8ibMaf7Kppq9MzdzI9/7pOffgdSn+P8J64CJAk3VrVswVgfy8lABt7fL8R6XReI9x8ewwKHhCRTwBgQIDAQAB';
        
        // Aadhar number to be encrypted
        $aadhar_no = $aadhaarNumber;  // Replace with your Aadhar number
        
        // Decode public key and create a key object
        $public_key_obj = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . chunk_split($public_key, 64, "\n") . "-----END PUBLIC KEY-----");
        
        
        if ($public_key_obj === false) {
        die('Public key loading failed');
        }
        
        // Encrypt Aadhar number using public key
        openssl_public_encrypt($aadhar_no, $encrypted_aadhar, $public_key_obj);
        
        // Base64 encode the encrypted Aadhar number
        $encoded_encrypted_aadhar = base64_encode($encrypted_aadhar);
        
        // echo "<br>";
        // print_r($encoded_encrypted_aadhar);
        // echo "<br>";
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $parameters = [
            'initiator_id' => $initiator_id,
            'customer_id' => $mobile,
            'aadhar' => $encoded_encrypted_aadhar,
            'user_code' => '35607002',
            'latlong' => '23.0755963,70.0549354'
        ];
        $api_params = http_build_query($parameters, '', '&');
        // $parameters = "initiator_id=$initiator_id&customer_id=$mobile&aadhar=$encodedMessage&user_code=35607002&latlong=23.0755963,70.0549354";
        $api_url = env('EKO_AEPS_URL2')."aeps/otp";
            
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($api_params);
        $api_response = $this->ApiCalls->ekoGetWithParaCall($api_url,$api_params);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        print_r($api_response);
    }
    
    public function aepsEkycOtpVerify(Request $request) {
    
        $aadhaarNumber = $request->aadhar;
        $mobile = $request->mobile;
        $otp = '152609';//$request->otp;
        $otp_ref_id = "3561220";//$request->otp_ref_id;
        $reference_tid = "EKYKF6179991080824202954206I";
        // Public key
        $public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCaFyrzeDhMaFLx+LZUNOOO14Pj9aPfr+1WOanDgDHxo9NekENYcWUftM9Y17ul2pXr3bqw0GCh4uxNoTQ5cTH4buI42LI8ibMaf7Kppq9MzdzI9/7pOffgdSn+P8J64CJAk3VrVswVgfy8lABt7fL8R6XReI9x8ewwKHhCRTwBgQIDAQAB';
        
        // Aadhar number to be encrypted
        $aadhar_no = $aadhaarNumber;  // Replace with your Aadhar number
        
        // Decode public key and create a key object
        $public_key_obj = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . chunk_split($public_key, 64, "\n") . "-----END PUBLIC KEY-----");
        
        
        if ($public_key_obj === false) {
        die('Public key loading failed');
        }
        
        // Encrypt Aadhar number using public key
        openssl_public_encrypt($aadhar_no, $encrypted_aadhar, $public_key_obj);
        
        // Base64 encode the encrypted Aadhar number
        $encoded_encrypted_aadhar = base64_encode($encrypted_aadhar);
        
        // echo "<br>";
        // print_r($encoded_encrypted_aadhar);
        // echo "<br>";
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $parameters = [
            'initiator_id' => $initiator_id,
            'customer_id' => $mobile,
            'aadhar' => $encoded_encrypted_aadhar,
            'otp' => $otp,
            'otp_ref_id' => $otp_ref_id,
            'reference_tid' => $reference_tid,//EKYKF6179991080824200254237I
            'user_code' => '35607002',
            'latlong' => '23.0755963,70.0549354'
        ];
        $api_params = http_build_query($parameters, '', '&');
        // $parameters = "initiator_id=$initiator_id&customer_id=$mobile&aadhar=$encodedMessage&user_code=35607002&latlong=23.0755963,70.0549354";
        $api_url = env('EKO_AEPS_URL2')."aeps/otp/verify";
            
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($api_params);
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$api_params);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        print_r($api_response);
    }
    
    public function dmtCustomerLogin($data) {
        $mobile = $data['mobile'];
        $user_code = $data['user_code'];
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $api_url = env('EKO_DMT_URL')."customers/mobile_number:$mobile?initiator_id=$initiator_id&user_code=$user_code";
        
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        $api_response = $this->ApiCalls->ekoGetCall($api_url);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtBenf($data) {
        $mobile = $data['mobile'];
        $user_code = $data['user_code'];
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $api_url = env('EKO_DMT_URL')."customers/mobile_number:$mobile/recipients?initiator_id=$initiator_id&user_code=$user_code";
        
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        $api_response = $this->ApiCalls->ekoGetCall($api_url);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtDeleteBenf($data) {
        $mobile = $data['mobile'];
        $recipient_id = $data['recipient_id'];
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        $api_url = env('EKO_DMT_URL')."customers/mobile_number:$mobile/recipients/recipient_id:$recipient_id";
        
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        $api_response = $this->ApiCalls->ekoDeleteCall($api_url);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtAddBenf($data) {
        $mobile = $data['mobile_number'];
        $acc_ifsc = $data['acc_ifsc'];
        $user_code = $data['user_code'];
        unset($data['acc_ifsc']);
        unset($data['mobile_number']);
        $api_url = env('EKO_DMT_URL')."customers/mobile_number:$mobile/recipients/acc_ifsc:$acc_ifsc";
        $api_params = http_build_query($data, '', '&');
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($api_params);
        $api_response = $this->ApiCalls->ekoPutCall($api_url,$api_params);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtTransaction($data) {
        
        $api_url = env('EKO_DMT_URL')."transactions";
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($data);
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$data);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtRefund($data) {
        $transaction_id = $data['transaction_id'];
        unset($data['transaction_id']);
        $api_url = env('EKO_DMT_URL')."transactions/$transaction_id/refund";
        $api_params = http_build_query($data, '', '&');
        Log::channel('ekoapi')->info("REQUEST");
        Log::channel('ekoapi')->info($api_url);
        Log::channel('ekoapi')->info($data);
        $api_response = $this->ApiCalls->ekoPostCall($api_url,$api_params);
        Log::channel('ekoapi')->info("RESPONSE");
        Log::channel('ekoapi')->info($api_response);
        return $api_response;
    }
    
    public function dmtCallbackUrl(Request $request) {
        
        $body = file_get_contents('php://input');
        
        Log::channel('ekoapi')->info("DMT CALLBACK RESPONSE");
        Log::channel('ekoapi')->info($body);
    }
}
