<?php

namespace App\Http\Controllers;
use App\Classes\UserAuth;
use App\Classes\ApiCalls;
use App\Http\Controllers\BillavenueController;
use App\Http\Controllers\EkoController;
use App\Http\Controllers\BulkpeController;
use App\Http\Controllers\CyrusController;
use App\Classes\WalletCalculation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Simple\Manager;
use Log;
use Dipesh79\LaravelPhonePe\LaravelPhonePe;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Models\User;
use App\Models\user_devices;
use App\Models\Addresses;
use App\Models\eko_services;
use App\Models\kyc_docs;
use App\Models\cities;
use App\Models\states;
use App\Models\banks;
use App\Models\dmt_customers;
use App\Models\dmt_beneficiaries;
use App\Models\shop_details;
use App\Models\transactions_dmt;
use App\Models\transactions_aeps;
use App\Models\dmt_upi_beneficiaries;
use App\Models\fund_banks;
use App\Models\fund_requests;
use App\Models\fund_onlines;

class ApiController extends Controller
{
    public function __construct(UserAuth $Auth, WalletCalculation $WalletCalculation, ApiCalls $ApiCalls, BillavenueController $BillavenueController, EkoController $EkoController, BulkpeController $BulkpeController, CyrusController $CyrusController){
        $this->UserAuth = $Auth;
        $this->WalletCalculation = $WalletCalculation;
        $this->BillavenueController = $BillavenueController;
        $this->EkoController = $EkoController;
        $this->BulkpeController = $BulkpeController;
        $this->CyrusController = $CyrusController;
        $this->ApiCalls = $ApiCalls;
    }
    
    public function getToken(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            "token_pw" => "required"
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        $plainPassword = $request->token_pw;
        $hashedPassword = '$2a$12$i6H2ti17jyVSU6qgUcP1meA1.KVQrbWfSGFJ2PJfFYo6xPAyOXRhC';
        if ($hashedPassword === $plainPassword) {
            $currentDate = app('currentDate');
            $newpassword = 'P@yr!t'.$currentDate->format('Y-m-d');
            return response()->json(['success' => true, 'message' => 'login successfully','new'=>$newpassword]);
        } else {
            
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
        
    }
    
    public function token(Request $request)
    {
        #PASSWORD#mobile#DDMMYYHHMM
        #Pyr!$@#9876543210#010120241212
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "user_password" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        Log::info($request->user_mobile."//".$request->user_password);
        if($check_token == 1){
            $plainPassword = $request->user_password;
            $user = User::select('id','user_id','name','surname','email','mobile','dob','first_login','password')->where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
            $hashedPassword = $user->password;
            if (Hash::check($plainPassword, $hashedPassword)) {
                // Passwords match
                $check_device = user_devices::where('user_id',$user->id)->where('device',$request->device_token)->first();
                if(!$check_device){
                    $ins_device = new user_devices();
                    $ins_device->user_id = $user->id;
                    $ins_device->device = $request->device_token;
                    $ins_device->save();
                }
                $wallet = User::find($user->id);
                return response()->json(['success' => true, 'message' => 'login successfully','data'=>$user,'wallet'=>$user->wallet->balanceFloat]);
            } else {
                // Passwords do not match;
                return response()->json(['success' => false, 'message' => 'Password Mismatch']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }
    }
    
    public function getBalance(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $wallet = User::find($user->id);
        $kyc = kyc_docs::where('user_id',$user->id)->first();
        $eko_services = eko_services::where('user_id',$user->id)->first();
        if($kyc){
            $kyc_status = $kyc->status;
        }else{
            $kyc_status = 3;
        }
        
        if($eko_services){
            $cyrus_aeps = $eko_services->cyrus_aeps;
            $cyrus_aeps_daily = $eko_services->cyrus_aeps_daily;
        }else{
            $cyrus_aeps = 0;
            $cyrus_aeps_daily = 0;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','kyc_status'=>$kyc_status,'wallet'=>$wallet->wallet->balanceFloat,'cyrus_aeps'=>$cyrus_aeps,'aeps_daily'=>$cyrus_aeps_daily]);
        
    }
    
    public function walletTest(Request $request)
    {
        exit;
        $user = User::find(36);

        // Create a wallet for the user
        $wallet = Wallet::findOrNew($user);
        
        // Check if the user has a wallet
        if (!$wallet) {
            // If the user doesn't have a wallet, create one
            $wallet = Wallet::create(['user_id' => $userId]);
        } else {
            // If the user already has a wallet, fetch it
            $wallet = $user->wallet;
        }
        
        // Deposit funds into the user's wallet
        // $wallet->depositFloat(1000.50);
        
        $wallet->withdrawFloat(49490,[
                        'meta' => [
                            'Title' => 'Admin Debit',
                            'detail' => 'Admin_Debit_On_27_07_2024',
                            'transaction_id' => 1,
                        ]
                    ]);
        
        // Deduct funds from the user's wallet
        // $wallet->withdrawFloat(5.4);
        
        // Retrieve the current balance of the user's wallet
        $balance = $wallet->balanceFloat;
        
        echo $balance;
    }
    
    public function getProfile(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $baseurl2 = env('APP_URL').'/uploads/profile_pics/';
        $profile_pic = DB::raw("CONCAT('$baseurl2',users.profile_image) AS profile_pic");
        $user = User::select('name','surname','email','mobile','dob',$profile_pic)->where('mobile',$request->user_mobile)->first();
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$user]);
    }
    
    public function getKyc(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $baseurl2 = env('APP_URL').'/uploads/kycdocs/';
        $pan = DB::raw("CONCAT('$baseurl2',kyc_docs.pan_image) AS pan_image");
        $aadhaar_front_image = DB::raw("CONCAT('$baseurl2',kyc_docs.aadhaar_front_image) AS aadhaar_front_image");
        $aadhaar_back_image = DB::raw("CONCAT('$baseurl2',kyc_docs.aadhaar_back_image) AS aadhaar_back_image");
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = kyc_docs::select($pan,$aadhaar_front_image,$aadhaar_back_image,'pan_number','aadhaar_number','status')->where('user_id',$user->id)->first();
        
        if($doc){
            $upload_status = $doc->status;
        }else{
            $doc = "";
            $upload_status = 3;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$doc,'upload_status'=>$upload_status]);
    }
    
    public function getShop(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $baseurl2 = env('APP_URL').'/uploads/shop/';
        $shop_img = DB::raw("CONCAT('$baseurl2',shop_details.shop_img) AS shop_img");
        $baseurl3 = env('APP_URL').'/uploads/selfie/';
        $selfie = DB::raw("CONCAT('$baseurl3',shop_details.selfie) AS selfie");
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = shop_details::select($selfie,$shop_img,'latitude','longitude','shop_name','shop_address','contact_number','status')->where('user_id',$user->id)->first();
        
        if($doc){
            $upload_status = $doc->status;
        }else{
            $doc = "";
            $upload_status = 3;
        }
        
        return response()->json(['success' => true, 'message' => 'successfully','data'=>$doc,'upload_status'=>$upload_status]);
    }
    
    public function updateKyc(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "pan_image" => "required",
            "aadhaar_front_image" => "required",
            "aadhaar_back_image" => "required",
            "pan_number" => "required",
            "aadhaar_number" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = kyc_docs::where('user_id',$user->id)->where('status',3)->first();
        if($doc){
            
                
                if ($request->input('pan_image')) {
                    $image = $request->input('pan_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename = 'PAN' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_front_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharfimagename = 'ADHARF' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharfimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_back_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharbimagename = 'ADHARB' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharbimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                $doc->pan_image = $panimagename;
                $doc->aadhaar_front_image = $aadharfimagename;
                $doc->aadhaar_back_image = $aadharbimagename;
                $doc->pan_number = $request->get("pan_number");
                $doc->aadhaar_number = $request->get("aadhaar_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'KYC Update successfully']);
        }else{
            
                if ($request->input('pan_image')) {
                    $image = $request->input('pan_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $panimagename = 'PAN' . $user->mobile . time() . '.jpg';
                    $filePath = $panimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_front_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharfimagename = 'ADHARF' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharfimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('aadhaar_back_image')) {
                    $image = $request->input('aadhaar_front_image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $aadharbimagename = 'ADHARB' . $user->mobile . time() . '.jpg';
                    $filePath = $aadharbimagename;
                    Storage::disk('public_img_kyc')->put($filePath, base64_decode($image));
                }
                $doc = new kyc_docs();
                $doc->user_id = $user->id;
                $doc->pan_image = $panimagename;
                $doc->aadhaar_front_image = $aadharfimagename;
                $doc->aadhaar_back_image = $aadharbimagename;
                $doc->pan_number = $request->get("pan_number");
                $doc->aadhaar_number = $request->get("aadhaar_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'KYC Update successfully']);
        }
            
    }
    
    public function updateStore(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "shop_name" => "required",
            "shop_address" => "required",
            "shop_img" => "required",
            "selfie" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "contact_number" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        $doc = shop_details::where('user_id',$user->id)->where('status',3)->first();
        if($doc){
            
                
                if ($request->input('shop_img')) {
                    $image = $request->input('shop_img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $shopimagename = 'SHOP' . $user->mobile . time() . '.jpg';
                    $filePath = $shopimagename;
                    Storage::disk('public_img_shop')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('selfie')) {
                    $image = $request->input('selfie');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $selfieimagename = 'SELFIE' . $user->mobile . time() . '.jpg';
                    $filePath = $selfieimagename;
                    Storage::disk('public_img_selfie')->put($filePath, base64_decode($image));
                }
                
                $doc->shop_name = $request->get("shop_name");
                $doc->shop_address = $request->get("shop_address");
                $doc->shop_img = $shopimagename;
                $doc->selfie = $selfieimagename;
                $doc->latitude = $request->get("latitude");
                $doc->longitude = $request->get("longitude");
                $doc->contact_number = $request->get("contact_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'Shop Detail Update successfully']);
        }else{
            
                if ($request->input('shop_img')) {
                    $image = $request->input('shop_img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $shopimagename = 'SHOP' . $user->mobile . time() . '.jpg';
                    $filePath = $shopimagename;
                    Storage::disk('public_img_shop')->put($filePath, base64_decode($image));
                }
                
                if ($request->input('selfie')) {
                    $image = $request->input('selfie');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $selfieimagename = 'SELFIE' . $user->mobile . time() . '.jpg';
                    $filePath = $selfieimagename;
                    Storage::disk('public_img_selfie')->put($filePath, base64_decode($image));
                }
                
                $doc = new shop_details();
                $doc->user_id = $user->id;
                $doc->shop_name = $request->get("shop_name");
                $doc->shop_address = $request->get("shop_address");
                $doc->shop_img = $shopimagename;
                $doc->selfie = $selfieimagename;
                $doc->latitude = $request->get("latitude");
                $doc->longitude = $request->get("longitude");
                $doc->contact_number = $request->get("contact_number");
                $doc->status = 0;
                $doc->save();
                
                return response()->json(['success' => true, 'message' => 'Shop Detail Update successfully']);
        }
            
    }
    
    public function updateProfileImage(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "image" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        if($user){
            
                
                if ($request->input('image')) {
                    
                    
                    $image = $request->input('image');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imagename = 'IMG' . $user->mobile . time() . '.jpg';
                    $filePath = $imagename;
                    Storage::disk('public_img_profile')->put($filePath, base64_decode($image));
                }
                
                $user->profile_image = $imagename;
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'Profile Image Update successfully']);
        }else{
            return response()->json(['success' => false, 'message' => "User Not Found"]);
        }
            
    }
    
    public function updateProfile(Request $request){
        
        if($request->get('c_password')){
            $validator = Validator::make($request->all(), [
                "token" => "required",
                "user_mobile" => "required|exists:users,mobile",
                "c_password" => "required",
                "new_password" => "required|string|min:8",
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $param_message = '';
                foreach ($errors->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $param_message .= " $message";
                    }
                }
                
                return response()->json(['success' => false, 'message' => $param_message]);
            }
            
            $user = User::where('status',1)->where('user_type',2)->where('mobile',$request->user_mobile)->first();
            $hashedPassword = $user->password;
            $plainPassword = $request->get('c_password');
            $newPassword = $request->get('new_password');
            if(Hash::check($plainPassword, $hashedPassword)) {
                $user->password = bcrypt($newPassword);
                $user->save();
                
                return response()->json(['success' => true, 'message' => "Password Update!"]);
            }else{
                return response()->json(['success' => false, 'message' => "Check Your Current Passowrd!"]);
            }
        }else{
            
        
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "name" => "required",
            "surname" => "required",
            "dob" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        if($user){
            
            
                $user->name = $request->get("name");
                $user->surname = $request->get("surname");
                $user->dob = $request->get("dob");
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'Profile Update successfully']);
        }else{
            return response()->json(['success' => false, 'message' => "User Not Found"]);
        }
        }
            
    }
    
    public function createCustomer(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "first_name" => "required",
            "address" => "required",
            "city" => "required",
            "state" => "required",
            "pincode" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        
        if($user){
            
            
            $check_customer = dmt_customers::where('mobile',$request->mobile)->where('status','1')->first();
            if(!$check_customer){
                $otp = rand(100000, 999999);
                $ins = dmt_customers::where('mobile',$request->mobile)->where('status','0')->first();
                if($ins){
                    $ins->user_id = $user->id;
                    $ins->mobile = $request->mobile;
                    $ins->first_name = $request->first_name;
                    $ins->last_name = $request->last_name;
                    $ins->address = $request->address;
                    $ins->city = $request->city;
                    $ins->state = $request->state;
                    $ins->pincode = $request->pincode;
                    $ins->status = 0;
                    $ins->otp = $otp;
                }else{
                    $ins = new dmt_customers();
                    $ins->user_id = $user->id;
                    $ins->mobile = $request->mobile;
                    $ins->first_name = $request->first_name;
                    $ins->last_name = $request->last_name;
                    $ins->address = $request->address;
                    $ins->city = $request->city;
                    $ins->state = $request->state;
                    $ins->pincode = $request->pincode;
                    $ins->status = 0;
                    $ins->otp = $otp;
                }
                
                $ins->save();
                
                $text = urlencode("Dear User, Use this OTP $otp to log in to your PAYRITE account. This OTP will be valid for the next 5 mins. https://payritepayment.in/");
                $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->mobile,$text,1207170972278643017);
                
                return response()->json(['success' => true, 'message' => 'Otp Send','is_reg'=>0]);
            }else{
                return response()->json(['success' => true, 'message' => 'Customer Already available','data'=>$check_customer,'is_reg'=>1]);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function otpCustomerVerify(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "otp" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $check_customer = dmt_customers::where('user_id',$user->id)->where('mobile',$request->mobile)->where('otp',$request->otp)->where('status','0')->first();
        if($check_customer){
            $check_customer->status = 1;
            $check_customer->save();
            $customer = dmt_customers::where('user_id',$user->id)->where('mobile',$request->mobile)->first();
            $dmt_limit = env('DMT_TXN_LIMIT');
            $dmt_use = $this->UserAuth->getCustomerUse($request->mobile);
            $available = $dmt_limit - $dmt_use;
            return response()->json(['success' => true, 'message' => 'OTP Verify','data'=>$customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else{
            return response()->json(['success' => true, 'message' => 'OTP Not Match','is_reg'=>0]);
        }
        
    }
    
    public function customerLogin(Request $request){
        return response()->json(['success' => false, 'message' => 'Service Is Dowm.']);
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $check_customer = dmt_customers::where('mobile',$request->mobile)->where('status','1')->first();
        if($user){
            if($check_customer){
                $dmt_limit = env('DMT_TXN_LIMIT');
                $dmt_use = $this->UserAuth->getCustomerUse($request->mobile);
                $available = $dmt_limit - $dmt_use;
                return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
            }else{
                return response()->json(['success' => true, 'message' => 'Customer Not Found','is_reg'=>0]);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function getBeneficiaries(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $get_beneficiaries = dmt_beneficiaries::where('customer_mobile',$request->customer_mobile)->where('status','1')->orderBy('id','DESC')->get();
        
        if($user){
            return response()->json(['success' => true, 'message' => 'Beneficiaries','data'=>$get_beneficiaries]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function addBeneficiary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "name" => "required",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_name" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        
        if($user){
            
            
                $ins = new dmt_beneficiaries();
                $ins->user_id = $user->id;
                $ins->customer_mobile = $request->customer_mobile;
                $ins->bank_name = $request->bank_name;
                $ins->account_holder_name = $request->name;
                $ins->account_number = $request->account;
                $ins->ifsc = $request->ifsc;
                $ins->is_verify = $request->is_verify;
                $ins->save();
                
                return response()->json(['success' => true, 'message' => 'Beneficiary Add']);
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function deleteBeneficiary(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "beneficiary_id" => "required|exists:dmt_beneficiaries,id",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        if($user){
            $get_benf = dmt_beneficiaries::where('id',$request->beneficiary_id)->where('customer_mobile',$request->customer_mobile)->first();
            $get_benf->status = 2;
            $get_benf->save();
            
            return response()->json(['success' => true, 'message' => 'Beneficiary Removed.']);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function doTransactions(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "beneficiary_id" => "required|exists:dmt_beneficiaries,id",
            "amount" => "required",
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $shop = shop_details::where('user_id',$user->id)->first();
        if($shop->latitude == null || $shop->longitude == null){
            return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
        }
        
        $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
        log::channel("location")->info("Retailer");
        log::channel("location")->info($request->latitude."/".$request->longitude);
        log::channel("location")->info("SHOP");
        log::channel("location")->info($shop->latitude."/".$shop->longitude);
        log::channel("location")->info("KM");
        log::channel("location")->info($distance);
        if($distance > 25){
            return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
        }
        
        $amount = $request->amount;
        $transfer_type = $request->transfer_type;
        
        $dmt_limit = env('DMT_TXN_LIMIT');
        $dmt_use = $this->UserAuth->getCustomerUse($request->mobile);
        $check_limit = $dmt_use + $amount;
        log::info("DMT LIMIT");
        log::info($check_limit."/".$request->mobile);
        if($dmt_limit <= $check_limit){
            return response()->json(['success' => false, 'message' => "Your Monthly Limit Is $dmt_limit."]);
        }
        
        if($amount >= 101 && $amount <= 1000){
            $fee = 12;
        }else{
            $fee = $amount * 1.2/100;
        }
        
        
        $totalamount = $amount + $fee;
        $gst = $fee - ($fee / 1.18);
        
        sleep(rand(1,6));
        if($user){
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            // $wallet = Wallet::findOrNew($user);
            $wallet = $userwallet->wallet;
            $txnid = $this->UserAuth->txnId('DMT');
            
            $get_benf = dmt_beneficiaries::find($request->beneficiary_id);
            
            
            $recentTime = Carbon::now()->subMinutes(10);
            
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('mobile', $request->customer_mobile)
                            ->where('ben_ac_number', $get_benf->account_number)
                            ->where('ben_ac_ifsc', $get_benf->ifsc)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            $txn_wl_fee = $wallet->withdrawFloat($fee,[
                'meta' => [
                    'Title' => 'Money Transfer Fee',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $wallet->withdrawFloat($amount,[
                'meta' => [
                    'Title' => 'Money Transfer',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = $get_benf->account_holder_name;
            $ins->ben_ac_number = $get_benf->account_number;
            $ins->ben_ac_ifsc = $get_benf->ifsc;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->wallets_uuid = $txn_wl->uuid;
            $ins->dmt_beneficiary_id = $get_benf->id;
            $ins->save();
            
            if ($duplicate) {
                return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            }
                            
            $api = "BULKPE";
            if($api == 'CYRUS'){
                
            }elseif($api == 'BULKPE'){
            $api_params = '{
                        "amount": "'.$amount.'",
                        "payment_mode": "'.$transfer_type.'",
                        "reference_id": "'.$txnid.'",
                        "transcation_note": "",
                        "beneficiaryName": "'.$get_benf->account_holder_name.'",
                        "upi": "",
                        "account_number": "'.$get_benf->account_number.'",
                        "ifsc": "'.$get_benf->ifsc.'"
                    }';
            
            $result = $this->BulkpeController->payoutTransaction($api_params);
            
            
            $json = json_decode($result);
            if(!isset($json->error)){
                if($json->data->status == "SUCCESS"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->response_reason = $json->data->payment_remark;
                    $update->utr = $json->data->utr;
                    $update->save();
                    $this->WalletCalculation->distributorDmt($txnid);
                    $this->WalletCalculation->retailorDmt($txnid);
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->data->status == "PENDING"){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = 0;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            }elseif($api == 'EKO'){
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            if($transfer_type == 'IMPS'){
                $payment_mode = 5;
            }else{
                $payment_mode = 4;
            }
            
            $post = [
                "initiator_id" => $initiator_id,
                "amount" => $amount,
                "payment_mode" => $payment_mode,
                "client_ref_id" => $txnid,
                "recipient_name" => $get_benf->account_holder_name,
                "ifsc" => $get_benf->ifsc,
                "account" => $get_benf->account_number,
                "service_code" => "45",
                "sender_name" => "Testing",
                "source" => "NEWCONNECT",
                "tag" => "retailer",
                "beneficiary_account_type" => "1"
            ];
            $api_params = http_build_query($post, '', '&');
            $result = $this->EkoController->payoutTransaction($api_params,'198481007');
            
            
            $json = json_decode($result);
            if($json->status == 0){
                if($json->data->tx_status == 0){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->utr = $json->data->tid;
                    $update->save();
                    $this->WalletCalculation->distributorDmt($txnid);
                    $this->WalletCalculation->retailorDmt($txnid);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Completed.', 'data'=>$data]);
                    
                }elseif($json->data->tx_status == 2){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Under Process.', 'data'=>$data]);
                }elseif($json->data->tx_status == 5){
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 5;
                    $update->utr = $json->data->tid;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Under Process.', 'data'=>$data]);
                }else{
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = $json->data->utr;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->utr = $json->data->utr;
                    $update->save();
                    
                    
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
    }
    
    public function dmtReport(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
            "dmt_api" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $api_id = $request->dmt_api;
            // $data = transactions_dmt::select('transactions_dmts.*',
            // DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
            // ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
            // ->join('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
            // ->where('transactions_dmts.user_id',$user->id)
            // ->where('dmt_customers.status','1')
            // ->whereColumn('dmt_beneficiaries.user_id','transactions_dmts.user_id')
            // ->whereColumn('dmt_customers.user_id','transactions_dmts.user_id')
            // ->whereBetween('transactions_dmts.created_at', array($from, $to))
            // ->orderBy('transactions_dmts.id','DESC')->get();
            
            $query = transactions_dmt::select(
                        'transactions_dmts.*',
                        DB::raw("CONCAT(users.name, ' ', users.surname) as retailer_name"),
                        'users.mobile as retailer_mobile',
                        'shop_details.shop_name'
                    )
                    ->join('users', 'users.id', 'transactions_dmts.user_id')
                    ->join('shop_details', 'shop_details.user_id', 'transactions_dmts.user_id')
                    ->where('transactions_dmts.user_id', $user->id)
                    ->where('transactions_dmts.event', 'DMT')
                    ->whereBetween('transactions_dmts.created_at', [$from, $to]);
            
            // Add joins only if transactions_dmts.api_id is not 2
            if ($api_id != 1) {
                $query->addSelect(
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),
                    'dmt_customers.mobile as customer_mobile',
                    'dmt_beneficiaries.bank_name as bank_name'
                )
                ->join('dmt_customers', 'dmt_customers.mobile', 'transactions_dmts.mobile')
                ->leftJoin('dmt_beneficiaries', 'dmt_beneficiaries.id', 'transactions_dmts.dmt_beneficiary_id')
                ->where('dmt_customers.status', 1)->where('transactions_dmts.api_id', 0);
            }else{
                $query->addSelect(
                    'transactions_dmts.sender_name as customer_name',
                    'transactions_dmts.mobile as customer_mobile',
                    'transactions_dmts.bank_name as bank_name'
                );
                $query->where('transactions_dmts.api_id', 1);
            }
            
            $data = $query->orderBy('transactions_dmts.id', 'DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function upiReport(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $data = transactions_dmt::select('transactions_dmts.*',
            DB::raw("CONCAT(users.name, ' ', users.surname) as customer_name"),'users.mobile as customer_mobile','dmt_beneficiaries.bank_name')
            ->join('users','users.id','transactions_dmts.user_id')
            ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
            ->where('transactions_dmts.user_id',$user->id)
            ->where('transactions_dmts.event','SCANNPAY')
            ->whereBetween('transactions_dmts.created_at', array($from, $to))
            ->orderBy('transactions_dmts.id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function myStatment(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $user_id = $user->id;
            $data = Transaction::select('transactions.*',DB::raw('ROUND(transactions.amount / 100, 2) as amount'),DB::raw('ROUND(transactions.balance / 100, 2) as balance'))
            ->leftJoin('fund_ods as fo', 'transactions.uuid', '=', 'fo.wallets_uuid')
            ->leftJoin('fund_onlines as fon', 'transactions.uuid', '=', 'fon.wallets_uuid')
            ->leftJoin('fund_requests as fr', 'transactions.uuid', '=', 'fr.wallets_uuid')
            ->leftJoin('transactions_aeps as ta', 'transactions.uuid', '=', 'ta.wallets_uuid')
            ->leftJoin('transactions_dmts as td', 'transactions.uuid', '=', 'td.wallets_uuid')
            ->where('transactions.payable_id',$user_id)
            ->where('payable_type', User::class)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.id', 'desc')
            ->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function aepsReport(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $from = $this->UserAuth->fetchFromDate($request->start_date);
            $to = $this->UserAuth->fetchToDate($request->end_date);
            $data = transactions_aeps::select('transactions_aeps.*')
            ->where('transactions_aeps.user_id',$user->id)
            ->whereBetween('transactions_aeps.created_at', array($from, $to))
            ->orderBy('transactions_aeps.id','DESC')->get();
            
            return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function accountVerification(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "account" => "required",
            "ifsc" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        if($user){
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            $amount = 4;
            // print_r($balance);exit;
            if($balance < $amount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            
            $txnid = $this->UserAuth->txnId('BCV');
            $post = '{
    "account_number": "'.$request->account.'",
    "ifsc": "'.$request->ifsc.'",
    "reference_id": "'.$txnid.'"
}';
            $data = $this->BulkpeController->bankVerification($post);
            $json = json_decode($data);
            
            if($json->statusCode == 200){
                $userwallet = User::find($user->id);
                $wallet = $userwallet->wallet;
                $totalamount = 4;
                $txn_wl = $wallet->withdrawFloat($totalamount,[
                    'meta' => [
                        'Title' => 'Account Verification',
                        'detail' => 'Debit_Retailer_'.$user->mobile.'_Account_Verification_Fee_'.$request->account,
                        'transaction_id' => $txnid,
                    ]
                ]);
                
                $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                
                $update = new transactions_dmt();
                $update->user_id = $user->id;
                $update->event = 'ACCOUNTVERIFY';
                $update->transaction_id = $txnid;
                $update->amount = $totalamount;
                $update->fee = 0;
                $update->tds = 0;
                $update->gst = 0;
                $update->ben_name = $json->data->account_holder_name;
                $update->ben_ac_number = $request->account;
                $update->ben_ac_ifsc = $request->ifsc;
                $update->closing_balance = $wallet->balanceFloat;
                $update->utr = 0;
                $update->status = 0;
                $update->status = 1;
                $update->utr = 0;
                $update->wallets_uuid = $txn_wl->uuid;
                $update->save();
                    
                    
                return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$json->data->account_holder_name]);
            }else{
                return response()->json(['success' => false, 'message' => 'Try again']);
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function getBanks(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $data = banks::orderBy('name','ASC')->get();
        
        return response()->json(['success' => true, 'message' => 'Transactions.', 'data'=>$data]);
    }
    
    public function doTransactionsUpi(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "upi_id" => "required",
            "beneficiary_name" => "required",
            "amount" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $shop = shop_details::where('user_id',$user->id)->first();
        if($shop->latitude == null || $shop->longitude == null){
            return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
        }
        
        $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
        log::channel("location")->info("Retailer");
        log::channel("location")->info($request->latitude."/".$request->longitude);
        log::channel("location")->info("SHOP");
        log::channel("location")->info($shop->latitude."/".$shop->longitude);
        log::channel("location")->info("KM");
        log::channel("location")->info($distance);
        if($distance > 25){
            return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
        }
        
        $amount = $request->amount;
        $transfer_type = 'UPI';
        
        if($amount >= 1 && $amount <= 1000){
            $fee = 12;
        }else{
            $fee = $amount * 1.2/100;
        }
        
        $totalamount = $amount + $fee;
        $gst = $fee - ($fee / 1.18);
        sleep(rand(1,6));
        
        if($user){
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            
            $recentTime = Carbon::now()->subMinutes(10);
            
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('ben_ac_number', $request->upi_id)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            // $wallet = Wallet::findOrNew($user);
            $wallet = $userwallet->wallet;
            $txnid = $this->UserAuth->txnId('UPI');
            
            $txn_wl_fee = $wallet->withdrawFloat($fee,[
                'meta' => [
                    'Title' => 'Scan And Pay Fee',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $wallet->withdrawFloat($amount,[
                'meta' => [
                    'Title' => 'Scan And Pay',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'SCANNPAY';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->user_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = $request->beneficiary_name;
            $ins->ben_ac_number = $request->upi_id;
            $ins->ben_ac_ifsc = 0;
            $ins->utr = 0;
            $ins->status = 0;
            $ins->wallets_uuid = $txn_wl->uuid;
            $ins->dmt_beneficiary_id = 0;
            $ins->save();
            
            $check_upi_in_bene = dmt_upi_beneficiaries::where('user_id',$user->id)->where('account_number',$request->upi_id)->first();
            if(!$check_upi_in_bene){
                $ins_ben = new dmt_upi_beneficiaries();
                $ins_ben->user_id = $user->id;
                $ins_ben->account_number = $request->upi_id;
                $ins_ben->account_holder_name = $request->beneficiary_name;
                $ins_ben->save();
            }
            
            $api = 'CYRUS';
            if($api == 'CYRUS'){
                $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'rRm8!GGoOC7Zp87',
                                'MethodName' => 'paytransfer',
                                'orderId' => $txnid,
                                'vpa' => $request->upi_id,
                                'Name' => $request->beneficiary_name,
                                'amount' => $amount,
                                'MobileNo' => $user->mobile,
                                'TransferType' => 'UPI');
            
                $result = $this->CyrusController->payoutTransaction($api_params);
                
                
                $json = json_decode($result);
                if($json->statuscode == "DE_001" || $json->statuscode == "DE_101"){
                    if($json->statuscode == "DE_001"){
                        //success
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 1;
                        $update->response_reason = $json->status;
                        $update->utr = $json->data->rrn;
                        $update->save();
                        $this->WalletCalculation->distributorUpi($txnid);
                        $this->WalletCalculation->retailorUpi($txnid);
                        // $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                        // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->user_mobile,$text,1207172205377529606);
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        return response()->json(['success' => true, 'message' => 'Transaction Done.', 'data'=>$data]);
                        
                    }elseif($json->statuscode == "DE_101"){
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }
                    }else{
                        //Refund Proccess
                        $txnid_ref = $this->UserAuth->txnId('REF');
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    
                    }
            }elseif($api == 'BULKPE'){
            $api_params = '{
                        "amount": "'.$amount.'",
                        "payment_mode": "UPI",
                        "reference_id": "'.$txnid.'",
                        "transcation_note": "",
                        "beneficiaryName": "'.$request->beneficiary_name.'",
                        "upi": "'.$request->upi_id.'",
                        "account_number": "",
                        "ifsc": ""
                    }';
            
            $result = $this->BulkpeController->payoutTransaction($api_params);
            
            
            $json = json_decode($result);
            if(!isset($json->error)){
                if($json->data->status == "SUCCESS"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->response_reason = $json->data->payment_remark;
                    $update->utr = $json->data->utr;
                    $update->save();
                    $this->WalletCalculation->distributorUpi($txnid);
                    $this->WalletCalculation->retailorUpi($txnid);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->data->status == "PENDING"){
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                    //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                    ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                    ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
            }
            
            }else{
                $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                $post = [
                    "initiator_id" => $initiator_id,
                    "customer_id" => $initiator_id,
                    "amount" => $amount,
                    "client_ref_id" => $txnid,
                    "recipient_name" => $request->beneficiary_name,
                    "customer_vpa" => $request->upi_id
                ];
                $api_params = http_build_query($post, '', '&');
                $result = $this->EkoController->payoutTransactionUpi($api_params,'198481007');
                
                
                $json = json_decode($result);
                if($json->status == 0){
                    if($json->data->tx_status == 0){
                        //success
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 1;
                        $update->utr = $json->data->tid;
                        $update->save();
                        $this->WalletCalculation->distributorUpi($txnid);
                        $this->WalletCalculation->retailorUpi($txnid);
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                        
                    }elseif($json->data->tx_status == 2){
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }elseif($json->data->tx_status == 5){
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 5;
                        $update->utr = $json->data->tid;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }else{
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    }
                    
                }else{
                        //Refund Proccess
                        
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Scan And Pay Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_CustomerFee_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Scan And Pay',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_Scan_and_pay_'.$transfer_type.'_Amount_'.$request->upi_id,
                                'transaction_id' => $txnid,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->utr = 0;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        DB::raw("CONCAT(dmt_customers.first_name, ' ', dmt_customers.last_name) as customer_name"),'dmt_customers.mobile as customer_mobile','dmt_beneficiaries.bank_name')
                        ->join('dmt_customers','dmt_customers.mobile','transactions_dmts.mobile')
                        ->leftjoin('dmt_beneficiaries','dmt_beneficiaries.id','transactions_dmts.dmt_beneficiary_id')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
            }
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
    }
    
    public function getFundBanks(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        
       
        
        $data = fund_banks::where('user_id',1)->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function listMoneyRequest(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = fund_requests::where('user_id',$user->id)->whereBetween('fund_requests.created_at', array($from, $to))->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function pgListMoneyRequest(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "start_date" => "required",
            "end_date" => "required",
        ]);
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $from = $this->UserAuth->fetchFromDate($request->start_date);
        $to = $this->UserAuth->fetchToDate($request->end_date);
        
        $data = fund_onlines::where('user_id',$user->id)->whereBetween('fund_onlines.created_at', array($from, $to))->get();
        
        return response()->json(['success' => true, 'message' => 'banks.', 'data'=>$data]);
    }
    
    public function createMoneyRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "amount" => "required",
            "transfer_type" => "required",
            "bank_id" => "exists:fund_banks,id",
            "deposit_date" => "required",
            "img" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {   
            $amount = $request->get("amount");
            $transfer_type = $request->get("transfer_type");
            $bank_id = $request->get("bank_id");
            $bank_ref = $request->get("bank_ref");
            $deposit_date = $request->get("deposit_date");
            $remark = $request->get("remark");
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $user_id = $user->id;
            
            if($amount > 0) {
                
                
                if($transfer_type != 'Cash'){
                    
                    $check_entry = fund_requests::where('bank_id',$bank_id)->where('bank_ref',$bank_ref)->whereIn('status',[1,0])->first();
                    if($check_entry) {
                        return response()->json(['success' => false, 'message' => 'You can not request with same bank and refrence id!']);
                    }
                }else{
                    $transfer_type = 'Bank';
                }
                
                $txn = $this->UserAuth->txnId("MR");
                $imagename = '';
                
                if ($request->input('img')) {
                    // $file = $request->file('img');
                    // $destinationPath = public_path('/uploads/money_request/');
                    // $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                    // $file->move($destinationPath, $imagename);
                    
                    $image = $request->input('img');
                    $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imagename = 'IMG' . $user_id . time() . '.jpg';
                    $filePath = $imagename;
                    Storage::disk('public_img')->put($filePath, base64_decode($image));
                }
                
                $money_request = new fund_requests();
                $money_request->user_id = $user_id;
                $money_request->transaction_id = $txn;
                $money_request->amount = $amount;
                $money_request->transfer_type = $transfer_type;
                $money_request->bank_id = $bank_id;
                $money_request->bank_ref = $bank_ref;
                $money_request->deposit_date = $deposit_date;
                $money_request->remark = $remark;
                $money_request->status = 0;
                $money_request->img = $imagename;
                $money_request->save();
                    
                if($money_request)
                    return response()->json(['success' => true, 'message' => 'Money request sent successfully','data'=>$money_request]);
                else
                    return response()->json(['success' => false, 'message' => 'Money request not sent!']);
            }
            else{
                return response()->json(['success' => false,'message' => 'Please enter valid amount!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function phonepePGData(Request $request) {
        
        $user = User::where('mobile',$request->user_mobile)->first();
        $user_id = $user->id;
        $amount = $request->amount;    
        $transactionId = $this->UserAuth->txnId('PHN');
        $ins = new fund_onlines();
        $ins->user_id = $user_id;
        $ins->transaction_id = $transactionId;
        $ins->amount = $amount;
        $ins->save();
        
        return response()->json(['success' => true,'message' => 'AEPS Keys data', 
            'merchantId' => 'M22WE8KIMIHEL',
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => 'MUID123',
            'developer_key' => '4ac75d00-2767-486d-8f38-69cec9af394d',
            'callbackUrl' => 'https://user.payritepayment.in/api/v1/callback/phonepe',
            'environment' => "production",
            'host_url' => "https://api.phonepe.com/apis/hermes",
            'transaction_id' => $transactionId,
            
            ]);
    }
    
    public function callbackPhonePe(Request $request) {
        // $phonepe = new LaravelPhonePe();
        //  $response = $phonepe->getTransactionStatus($request->all());
        //  if($response == true){
        //     //Payment Success
        //  }
        //  else
        //  {
        //     //Payment Failed           
        //  }
         
        $payload = $request->all();
        $callbackResponse = file_get_contents('php://input');
        
        // Log::info($callbackResponse);
        
        $payload_decode = json_decode($callbackResponse, true);
        // Log::info($payload_decode);
        // Step 1: Decode the base64-encoded string
        $jsonString = base64_decode($payload_decode['response']);
        
        // Step 2: Decode the JSON string into a PHP associative array
        $responseArray = json_decode($jsonString, true);
        Log::info('Received PhonePe callback Decode:');
        Log::info($responseArray);
        // Check for JSON decoding errors
        if($responseArray['success']){
            if($responseArray['data']['state'] == 'COMPLETED'){
                $transaction_id = $responseArray['data']['merchantTransactionId'];
                $find = fund_onlines::where('transaction_id',$transaction_id)->where('status',0)->first();
                if($find){
                    $userwallet = User::find($find->user_id);
                    $wallet = $userwallet->wallet;
                    $fee = 1.45;
                    $amount_main = $find->amount;
                    $fee_val = $amount_main * $fee / 100;
                    $amount = $amount_main - $fee_val;
                    $txn_wl = $wallet->depositFloat($amount_main,[
                        'meta' => [
                            'Title' => 'Online Load Phonepe',
                            'detail' => 'Credit_Phonepe_'.$responseArray['data']['transactionId'].'_Amount_'.$amount_main,
                            'transaction_id' => $transaction_id,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    $find->amount_txn = $amount;
                    $find->fee = $fee_val;
                    $find->ref_id = $responseArray['data']['transactionId'];
                    $find->closing_balance = $wallet->balanceFloat;
                    $find->status = 1;
                    $find->pg_id = 1;
                    $find->wallets_uuid = $txn_wl->uuid;
                    $find->save();
                    
                    $transactionId_fee = $this->UserAuth->txnId('FEE');
                    $txn_wl_fee = $wallet->withdrawFloat($fee_val,[
                        'meta' => [
                            'Title' => 'Online Load Phonepe Fee',
                            'detail' => 'Debit_Phonepe_'.$responseArray['data']['transactionId'].'_Fee_'.$fee_val,
                            'transaction_id' => $transactionId_fee,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    // $ins = new fund_onlines();
                    // $ins->user_id = $find->user_id;
                    // $ins->transaction_id = $transactionId_fee;
                    // $ins->amount = $amount;
                    // $ins->ref_id = $transaction_id;
                    // $ins->closing_balance = $wallet->balanceFloat;
                    // $ins->status = 1;
                    // $ins->pg_id = 1;
                    // $ins->wallets_uuid = $txn_wl_fee->uuid;
                    // $ins->save();
                    
                    
                }else{
                    Log::info('Transaction id not found phonepe:', $transaction_id);
                }
                
            }else{
                Log::info('Transaction failed phonepe:');
            }
            
        }else{
            Log::info('Transaction Wrong phonepe:');
        }
        return response()->json(['message' => 'Callback received successfully'], 200);
    }
    
    public function callbackBulkpeCredit(Request $request) {
        $payload = $request->all();
        Log::channel('bulkpeapi')->info("CALLBACK Credit");
        Log::channel('bulkpeapi')->info($payload);
    }
    
    public function callbackBulkpe(Request $request) {
        $payload = $request->all();
        Log::channel('bulkpeapi')->info("CALLBACK");
        Log::channel('bulkpeapi')->info($payload);
        
        $status = $payload['data']['trx_status'];
        
        if($status == "SUCCESS") {
            $txn_id = $payload['data']['reference_id'];
            $utr = $payload['data']['utr'];
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $mobile = $transaction->mobile;
                $transaction->status = 1;
                $transaction->utr = $utr;
                $transaction->save();
                $this->WalletCalculation->distributorDmt($txn_id);
                $this->WalletCalculation->retailorDmt($txn_id);
                $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txn_id on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($mobile,$text,1207172205377529606);
            }
            return response()->json(['success' => true, 'message' => 'success']);
        }
        
        if($status == "FAILED") {
            $txn_id = $payload['data']['reference_id'];
            $utr = $payload['data']['utr'];
            if(isset($payload['data']['trx_message'])) 
                $response_reason = $payload['data']['trx_message'];
            else
                $response_reason = '';
            
            $transaction = transactions_dmt::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                $amount = $transaction->amount;
                $fee = $transaction->fee;
                $userwallet = User::find($transaction->user_id);
                $wallet = $userwallet->wallet;
                $transfer_type = $transaction->transfer_type;
                
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$transaction->mobile,
                            'transaction_id' => $txn_id,
                        ]
                    ]);
                    $balance_update_fee = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$userwallet->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$transaction->mobile,
                            'transaction_id' => $txn_id,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $transaction->status = 2;
                    $transaction->utr = $utr;
                    $transaction->response_reason = $response_reason;
                    $transaction->save();
            }
        }
    }
    
    public function fcmtesting(Request $request) {
        $api_response = $this->ApiCalls->sendfcmNotification(3,"Payrite","Testing");
        
    }
    
    public function getStateOfCyrusState(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'AEPS3GETSTATE');
            
                $result = $this->CyrusController->getState($api_params);
                
                $data = json_decode($result);
                return response()->json(['success' => true, 'message' => '', 'data'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBankOfCyrus(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'AEPS3GETBANK');
            
                $result = $this->CyrusController->getBank($api_params);
                
                print_r($result);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function registrationCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "state" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $addresses = Addresses::join('cities','cities.id','addresses.city_id')->where('user_id',$user->id)->first();
            $shop = shop_details::where('user_id',$user->id)->first();
            $kyc = kyc_docs::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                        "MerchantID": "AP487545",
                        "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                        "uniqueid": "'.$user->user_id.'",
                        "uniquepin": "12345678",
                        "MethodName": "registration",
                        "Mobile": "'.$user->mobile.'",
                        "Email": "'.$user->mobile.'",
                        "Company": "'.$shop->shop_name.'",
                        "Name": "'.$user->name.' '.$user->surname.'",
                        "Pan": "'.$kyc->pan_number.'",
                        "Pincode": "'.$addresses->pincode.'",
                        "Address": "'.$shop->shop_address.'",
                        "Aadhar": "'.$kyc->aadhaar_number.'",
                        "state": "'.$request->state.'",
                        "city": "'.$addresses->name.'",
                        "latitude":"'.$shop->latitude.'", 
                        "longitude":"'.$shop->longitude.'"
                    }';
            
                $result = $this->CyrusController->registrationAeps($api_params);
                
                // print_r($result);
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_code = $data->data->primaryKeyId;
                        $service->cyrus_FPTxnId = $data->data->encodeFPTxnId;
                        $service->cyrus_aeps = 2;
                        $service->save();
                    }else{
                        $ins = new eko_services();
                        $ins->user_id = $user_id;
                        $ins->cyrus_code = $data->data->primaryKeyId;
                        $ins->cyrus_FPTxnId = $data->data->encodeFPTxnId;
                        $ins->cyrus_aeps = 2;
                        $ins->save();
                        
                    }
                    
                    return response()->json(['success' => true, 'message' => 'OTP Send On Your Number.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function otpVerifyCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "otp" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                            "MerchantID": "AP487545",
                            "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                            "merchantLoginId": "'.$user->user_id.'",
                            "OTP": "'.$request->otp.'",
                            "MethodName": "submitotp",
                            "primaryKeyId": "'.$service->cyrus_code.'",
                            "encodeFPTxnId": "'.$service->cyrus_FPTxnId.'"
                        }';
            
                $result = $this->CyrusController->registrationAeps($api_params);
                
                
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_aeps = 1;
                        $service->save();
                    }
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Registration Done.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = array('MerchantID' => 'AP487545',
                                'MerchantKey' => 'J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=',
                                'MethodName' => 'GETURL',
                                'OutLetID' => $user->user_id);
            
                $result = $this->CyrusController->getBank($api_params);
                
                
                $data = json_decode($result);
                if($data->statuscode == 10000){
                    
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Registration Done.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->status]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function dailyKycCyrusAeps(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "device_data" => "required",
            "aadhar" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_id = $user->id;
            
            $api_params = '{
                            "MerchantID": "AP487545",
                            "MerchantKey": "J9BvBJnUM377177YnJG8fpD+GoWcFAtwfzkV0abuFFg=",
                            "merchantLoginId": "'.$user->user_id.'",
                            "MethodName": "biomatrickyc",
                            "primaryKeyId": "'.$service->cyrus_code.'",
                            "encodeFPTxnId": "'.$service->cyrus_FPTxnId.'",
                            "captureResponse":'.$request->device_data.',
                            "cardnumberORUID": {
                                "adhaarNumber": "'.$request->aadhar.'",
                                "indicatorforUID": 0,
                                "nationalBankIdentificationNumber": "508534"
                            },
                            "requestRemarks": "6464",
                            "superMerchantId": 0
                        }';
            
                $result = $this->CyrusController->dailyKYC($api_params);
                
                
                $data = json_decode($result);
                if($data->statusCode == 10000){
                    $service = eko_services::where('user_id',$user->id)->first();
                    if($service){
                        $service->cyrus_aeps_daily = 1;
                        $service->save();
                    }
                    
                    return response()->json(['success' => true, 'message' => 'AEPS Daily KYC Complete.']);
                }else{
                    return response()->json(['success' => false, 'message' => $data->message]);
                }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function dmtTransactionStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $transaction_id = $request->transaction_id;
            $data = transactions_dmt::select('status')->where('transaction_id',$transaction_id)->first();
            
            return response()->json(['success' => true, 'message' => '', 'status'=>$data->status]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtCustomerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile" => $customer_mobile,
                "user_code" => $user_code
            ];
            $result = $this->EkoController->dmtCustomerLogin($post);
            $decode = json_decode($result);
            if($decode->response_status_id == 1 && $decode->response_type_id == -1){
                return response()->json(['success' => false, 'message' => 'Customer does not exist in System', 'data'=>$decode]);
            }
            $available = $decode->data->available_limit;
            $dmt_use = $decode->data->used_limit;
            $dmt_limit = $decode->data->total_limit;
            $check_customer = $decode->data;
            
            
            return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtGetBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile" => $customer_mobile,
                "user_code" => $user_code
            ];
            $result = $this->EkoController->dmtBenf($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtAddBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required|exists:dmt_customers,mobile",
            "name" => "required",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_id" => "required",
            "benf_mobile" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $name = $request->name;
            $bank_id = $request->bank_id;
            $ifsc = $request->ifsc;
            $account = $request->account;
            $benf_mobile = $request->benf_mobile;
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $post = [
                "mobile_number" => $customer_mobile,
                "user_code" => $user_code,
                "initiator_id" => $initiator_id,
                "recipient_mobile" => $benf_mobile,
                "bank_id" => $bank_id,
                "acc_ifsc" => $account."_".$ifsc,
                "recipient_name" => $name
            ];
            $result = $this->EkoController->dmtAddBenf($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtDoTransactions(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => "required",
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            if($distance > 25){
                return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
            }
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            
            if($amount >= 101 && $amount <= 1000){
                $fee = 12;
            }else{
                $fee = $amount * 1.2/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            sleep(rand(1,6));
            
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            $wallet = $userwallet->wallet;
            $recentTime = Carbon::now()->subMinutes(10);
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('mobile', $customer_mobile)
                            ->where('eko_beneficiary_id', $beneficiary_id)
                            ->where('amount', $amount)
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            $txnid = $this->UserAuth->txnId('DMT');
            $txn_wl_fee = $wallet->withdrawFloat($fee,[
                'meta' => [
                    'Title' => 'Money Transfer Fee',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $wallet->withdrawFloat($amount,[
                'meta' => [
                    'Title' => 'Money Transfer',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    'transaction_id' => $txnid,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnid;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ben_ac_ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->api_id = 1;
            $ins->wallets_uuid = $txn_wl->uuid;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->save();
            
            if ($duplicate) {
                return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $txnid = $this->UserAuth->txnId('DMT');
            $post = [
                "initiator_id" => $initiator_id,
                "customer_id" => $customer_mobile,
                "recipient_id" => $beneficiary_id,
                "amount" => $amount,
                "client_ref_id" => $txnid,
                "latlong" => $latitude." ".$longitude,
                "channel" => $payment_mode,
                "state" => 1,
                "timestamp" => date('Y-m-d H:i:s'),
                "currency" => "INR",
                "user_code" => $user_code
            ];
            $api_params = http_build_query($post, '', '&');
            $result = $this->EkoController->dmtTransaction($api_params);
            $json = json_decode($result);
            if($json->response_status_id == 0){
                if($json->data->tx_status == "0"){
                    //success
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $json->data->tid;
                    $update->ben_name = $json->data->recipient_name;
                    $update->ben_ac_number = $json->data->account;
                    $update->bank_name = $json->data->bank;
                    $update->sender_name = $json->data->sender_name;
                    $update->save();
                    $this->WalletCalculation->distributorDmt($txnid);
                    $this->WalletCalculation->retailorDmt($txnid);
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnid on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->data->tx_status == "2" || $json->data->tx_status == "5"){
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = $json->data->tid;
                    $update->ben_name = $json->data->recipient_name;
                    $update->ben_ac_number = $json->data->account;
                    $update->bank_name = $json->data->bank;
                    $update->sender_name = $json->data->sender_name;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = $json->data->tx_status;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
            }else{
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnid,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoDmtRefund(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "transaction_id" => "required|exists:transactions_dmts,transaction_id",
            "otp" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $transaction_id = $request->transaction_id;
            $otp = $request->otp;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $user = User::where('mobile',$request->user_mobile)->first();
            $service = eko_services::where('user_id',$user->id)->first();
            $user_code = $service->eko_code;
            
            $data = transactions_dmt::where('transaction_id',$transaction_id)->first();
            $tid = $data->utr;
            $post = [
                "otp" => $otp,
                "user_code" => $user_code,
                "initiator_id" => $initiator_id,
                "transaction_id" => $tid,
                "state" => 1
            ];
            $result = $this->EkoController->dmtRefund($post);
            $decode = json_decode($result);
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtCustomerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            
            $post = [
                "mobile" => $customer_mobile,
            ];
            $result = $this->BillavenueController->DmtCustomerLogin($post);
            $decode = json_decode($result);
            if($decode->responseReason == 'Failure'){
                return response()->json(['success' => true, 'message' => 'Customer does not exist in System', 'data'=>$decode,'is_reg'=>0]);
            }
            $available = $decode->availableLimit;
            $dmt_use = $decode->usedLimit;
            $dmt_limit = $decode->totalLimit;
            $check_customer = $decode;
            
            
            return response()->json(['success' => true, 'message' => 'Customer Login','data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtCreateCustomer(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "name" => "required|min:5",
            "pincode" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $mobile = $request->mobile;
        $name = $request->name;
        $pincode = $request->pincode;
        $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.]/', '', $name);
        if($user){
            
            $post = [
                "mobile" => $mobile,
                "name" => $name,
                "pincode" => $pincode,
            ];
            $result = $this->BillavenueController->DmtCreateCustomer($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => 'Otp Send','is_reg'=>0,'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong Try again','is_reg'=>0,'data'=>$decode]);
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function billDmtCustomerVerify(Request $request){
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "mobile" => "required",
            "otp" => "required",
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }
        
        $check_token = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        
        $user = User::where('mobile',$request->user_mobile)->first();
        
        $otp = $request->otp;
        $additional_reg_data = $request->additional_reg_data;
        $mobile = $request->mobile;
        
        if($user){
            if(empty($additional_reg_data)){
                $additional_reg_data = "NA";
            }
            
            $post = [
                "mobile" => $mobile,
                "otp" => $otp,
                "additional_reg_data" => $additional_reg_data,
            ];
            $result = $this->BillavenueController->DmtOtpCustomerVerify($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                $post = [
                    "mobile" => $mobile,
                ];
                $result = $this->BillavenueController->DmtCustomerLogin($post);
                $decode = json_decode($result);
                
                $available = $decode->availableLimit;
                $dmt_use = $decode->usedLimit;
                $dmt_limit = $decode->totalLimit;
                $check_customer = $decode;
                return response()->json(['success' => true, 'message' => 'Verify','is_reg'=>1,'data'=>$check_customer,'is_reg'=>1,'available'=>$available,'used'=>$dmt_use,'limit'=>$dmt_limit]);
            }else{
                return response()->json(['success' => false, 'message' => $decode->errorInfo->error->errorMessage,'is_reg'=>0,'data'=>$decode]);
            }
            
            
        }else{
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }
        
        
    }
    
    public function billDmtGetBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $post = [
                "mobile" => $customer_mobile,
            ];
            $result = $this->BillavenueController->DmtGetBeneficiaries($post);
            $decode = json_decode($result);
            
            if(isset($decode->recipientList->dmtRecipient)){
                $data = $decode->recipientList->dmtRecipient;
                if(is_array($data)){
                    
                }else{
                    $data = [$data];
                }
            }else{
                $data = [];
            }
            
            return response()->json(['success' => true, 'message' => '', 'data'=>$decode, 'recipientList'=>$data]);
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDeleteBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $user = User::where('mobile',$request->user_mobile)->first();
            
            $post = [
                "mobile" => $customer_mobile,
                "beneficiary_id" => $beneficiary_id,
            ];
            $result = $this->BillavenueController->DmtDeleteBeneficiary($post);
            $decode = json_decode($result);
            
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => 'Delete Beneficiary', 'data'=>'', 'recipientList'=>'']);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong']);
            }
            
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtAddBenf(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required",
            "customer_mobile" => "required",
            "name" => "required|min:5",
            "account" => "required",
            "ifsc" => "required",
            "is_verify" => "required",
            "bank_id" => "required",
            "benf_mobile" => "required",
        ]);


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $customer_mobile = $request->customer_mobile;
            $name = $request->name;
            $bank_id = $request->bank_id;
            $ifsc = $request->ifsc;
            $account = $request->account;
            $benf_mobile = $request->benf_mobile;
            
            $user = User::where('mobile',$request->user_mobile)->first();
            $name = preg_replace('/[\*\?\[\]\{\}\^\$\(\)\+\|,\.]/', '', $name);
            $post = [
                "mobile_number" => $customer_mobile,
                "recipient_mobile" => $benf_mobile,
                "bank_id" => $bank_id,
                "account" => $account,
                "ifsc" => $ifsc,
                "recipient_name" => $name
            ];
            $result = $this->BillavenueController->DmtAddBeneficiary($post);
            $decode = json_decode($result);
            if($decode->responseCode == '000'){
                return response()->json(['success' => true, 'message' => '', 'data'=>$decode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Something Wrong', 'data'=>$decode]);
            }
            
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function billDmtDoTransactions(Request $request) {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "user_mobile" => "required|exists:users,mobile",
            "customer_mobile" => "required",
            "beneficiary_id" => "required",
            "amount" => [
                            "required",
                            "numeric",
                            function($attribute, $value, $fail) {
                                // Check if the amount is greater than 100
                                if ($value < 100) {
                                    $fail($attribute . ' must be greater than 100.');
                                }
                            },
                        ],
            "transfer_type" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $param_message = '';
            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $param_message .= " $message";
                }
            }
            
            return response()->json(['success' => false, 'message' => $param_message]);
        }


        $response = $this->UserAuth->authUserToken($request->user_mobile,$request->token);
        $response = 1;

        if($response)
        {
            $user = User::where('mobile',$request->user_mobile)->first();
            // if($user->id != 21){
            //     return response()->json(['success' => false, 'message' => "Try after 11:00AM."]);
            // }
        
            $shop = shop_details::where('user_id',$user->id)->first();
            if($shop->latitude == null || $shop->longitude == null){
                return response()->json(['success' => false, 'message' => "Please Insert Shop Detail."]);
            }
            
            $distance = $this->UserAuth->geteDistance($shop->latitude, $shop->longitude, $request->latitude, $request->longitude);
            log::channel("location")->info("Retailer");
            log::channel("location")->info($request->latitude."/".$request->longitude);
            log::channel("location")->info("SHOP");
            log::channel("location")->info($shop->latitude."/".$shop->longitude);
            log::channel("location")->info("KM");
            log::channel("location")->info($distance);
            if($distance > 25){
                return response()->json(['success' => false, 'message' => "Your not at your shop address.$distance Km far from your shop"]);
            }
            
            $transfer_type = $request->transfer_type;
            $customer_mobile = $request->customer_mobile;
            $beneficiary_id = $request->beneficiary_id;
            $amount = $request->amount;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user = User::where('mobile',$request->user_mobile)->first();
            $amount_par = $this->UserAuth->partitionAmount($amount);
            $main_amount = $amount;
            $loop_count = count($amount_par);
            $txnid = $this->UserAuth->txnId('DMT');
            sleep(rand(2,6));
            $recentTime = Carbon::now()->subMinutes(10);
            $duplicate = transactions_dmt::where('user_id', $user->id)
                            ->where('mobile', $customer_mobile)
                            ->where('eko_beneficiary_id', $beneficiary_id)
                            ->whereIn('status', [0,1])
                            ->where('amount', $amount_par[0])
                            ->where('created_at', '>=', $recentTime)
                            ->exists();
            if ($duplicate) {
                log::info('Duplicate');
                log::info($request->all());
                return response()->json(['success' => false, 'message' => 'Duplicate! By Mistake Double Click or Duplicat Transaction. Please Check you report.']);
            }
            
            //txn start
            //FEE ST
            for($i = 0;$i < $loop_count;$i++){
                $txnidm = $txnid.'#'.$i;
                $txnidr = $this->UserAuth->txnId('DMT');
                $amount = $amount_par[$i];
                
            if($amount >= 100 && $amount <= 1000){
                if($main_amount > 1000){
                    $fee = $amount * 1.2/100;
                }else{
                    $fee = 12;
                }
                
            }else{
                $fee = $amount * 1.2/100;
            }
            $totalamount = $amount + $fee;
            $gst = $fee - ($fee / 1.18);
            sleep(rand(1,2));
            //FEE END
            $userwallet = User::find($user->id);
            $balance = $userwallet->wallet->balanceFloat;
            // print_r($balance);exit;
            if($balance < $totalamount){
                goto skip;
                
                return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            }
            $wallet = $userwallet->wallet;
            
            
            
            $txn_wl_fee = $wallet->withdrawFloat($fee,[
                'meta' => [
                    'Title' => 'Money Transfer Fee',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                    'transaction_id' => $txnidr,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
            
            $txn_wl = $wallet->withdrawFloat($amount,[
                'meta' => [
                    'Title' => 'Money Transfer',
                    'detail' => 'Debit_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                    'transaction_id' => $txnidr,
                ]
            ]);
            $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
            
            
            
            $ins = new transactions_dmt();
            $ins->user_id = $user->id;
            $ins->event = 'DMT';
            $ins->transaction_id = $txnidr;
            $ins->multi_transaction_id = $txnidm;
            $ins->amount = $amount;
            $ins->mobile = $request->customer_mobile;
            $ins->transfer_type = $transfer_type;
            $ins->fee = $fee;
            $ins->tds = 0;
            $ins->gst = $gst;
            $ins->closing_balance = $wallet->balanceFloat;
            $ins->ben_name = '';
            $ins->ben_ac_number = '';
            $ins->ben_ac_ifsc = '';
            $ins->utr = 0;
            $ins->status = 0;
            $ins->api_id = 1;
            $ins->wallets_uuid = $txn_wl->uuid;
            $ins->eko_beneficiary_id = $beneficiary_id;
            $ins->save();
            
            if ($duplicate) {
                // return response()->json(['message' => 'Duplicate entry detected'], 409); // HTTP 409 Conflict
            }
            
            if($transfer_type == 'IMPS'){
                $payment_mode = 2;
            }else{
                $payment_mode = 1;
            }
            $amount_bill = $amount * 100;
            
            $post_fee = [
                "amount" => $amount_bill,
            ];
            $result_fee = $this->BillavenueController->DmtGetCCFFee($post_fee);
            $json_fee = json_decode($result_fee);
            $con_fee = $json_fee->custConvFee;
            $post = [
                "customer_id" => $customer_mobile,
                "recipient_id" => $beneficiary_id,
                "amount" => $amount_bill,
                "client_ref_id" => $txnidr,
                "latlong" => $latitude." ".$longitude,
                "transfer_type" => $transfer_type,
                "con_fee" => $con_fee,
            ];
            
            $result = $this->BillavenueController->DmtdoTransactions($post);
            $json = json_decode($result);
            $result_benf = $this->BillavenueController->DmtGetBeneficiary($post);
            $json_bnef = json_decode($result_benf);
            $post_sender = [
                "mobile" => $customer_mobile,
            ];
            $result_sender = $this->BillavenueController->DmtCustomerLogin($post_sender);
            $json_sender = json_decode($result_sender);
            if($json->responseCode == '000' || $json->responseReason == '901'){
                
                if($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "C"){
                    //success
                    
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                        }else{
                            $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                        }
                    }else{
                        $utr = 0;
                    }
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 1;
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $this->WalletCalculation->distributorDmt($txnidr);
                    $this->WalletCalculation->retailorDmt($txnidr);
                    
                    $text = urlencode("Dear Customer Rs $amount transfer with transaction id $txnidr on ".date("Y-m-d")." Successfully Powered by Payrite Payment Solutions Pvt Ltd");
                    // $api_response = $this->ApiCalls->smsgatewayhubGetCall($request->customer_mobile,$text,1207172205377529606);
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    // return response()->json(['success' => true, 'message' => 'Transaction Success.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "P"){
                    
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                        }else{
                            $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                        }
                    }else{
                        $utr = 0;
                    }
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "Q"){
                    
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                        }else{
                            $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                        }
                    }else{
                        $utr = 0;
                    }
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    
                }elseif($json->responseCode == "901"){
                    
                    if(isset($json->fundTransferDetails->fundDetail)){
                        if(is_array($json->fundTransferDetails->fundDetail)){
                            $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                        }else{
                            $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                        }
                    }else{
                        $utr = 0;
                    }
                    $update = transactions_dmt::find($ins->id);
                    $update->eko_status = 0;
                    $update->utr = $utr;
                    $update->tid = $json->uniqueRefId;
                    $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    if($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "F"){
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Money Transfer Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Money Transfer',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                            }
                        }else{
                            $utr = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                    }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "R"){
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Money Transfer Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Money Transfer',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                            }
                        }else{
                            $utr = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                        
                    }elseif($json->responseCode == "000" && $json->fundTransferDetails->fundDetail->txnStatus == "S"){
                        $txn_wl_fee = $wallet->depositFloat($fee,[
                            'meta' => [
                                'Title' => 'Money Transfer Fee',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                        
                        $txn_wl = $wallet->depositFloat($amount,[
                            'meta' => [
                                'Title' => 'Money Transfer',
                                'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                                'transaction_id' => $txnidr,
                            ]
                        ]);
                        $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                            }
                        }else{
                            $utr = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->status = 2;
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                        
                    }else{
                        if(isset($json->fundTransferDetails->fundDetail)){
                            if(is_array($json->fundTransferDetails->fundDetail)){
                                $utr = $json->fundTransferDetails->fundDetail[0]->bankTxnId;
                            }else{
                                $utr =  $json->fundTransferDetails->fundDetail->bankTxnId;
                            }
                        }else{
                            $utr = 0;
                        }
                        $update = transactions_dmt::find($ins->id);
                        $update->eko_status = 0;
                        $update->utr = $utr;
                        $update->tid = $json->uniqueRefId;
                        $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                        $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                        $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                        $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                        $update->sender_name = $json_sender->senderName;
                        $update->save();
                        
                        $data = transactions_dmt::select('transactions_dmts.*',
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->where('transactions_dmts.id',$ins->id)
                        ->orderBy('transactions_dmts.id','DESC')->first();
                        
                        // return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                    }
                    
                }
            }else{
                    $txn_wl_fee = $wallet->depositFloat($fee,[
                        'meta' => [
                            'Title' => 'Money Transfer Fee',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_CustomerFee_'.$request->customer_mobile,
                            'transaction_id' => $txnidr,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl_fee->uuid)->update(['balance' => $wallet->balance]);
                    
                    $txn_wl = $wallet->depositFloat($amount,[
                        'meta' => [
                            'Title' => 'Money Transfer',
                            'detail' => 'Refund_Retailer_'.$user->mobile.'_DMT_Remittance_'.$transfer_type.'_Amount_'.$request->customer_mobile,
                            'transaction_id' => $txnidr,
                        ]
                    ]);
                    $balance_update = Transaction::where('uuid', $txn_wl->uuid)->update(['balance' => $wallet->balance]);
                    
                    $update = transactions_dmt::find($ins->id);
                    $update->status = 2;
                    $update->eko_status = 1;
                    $update->utr = 0;
                    $update->ben_name = $json_bnef->recipientList->dmtRecipient->recipientName;
                    $update->ben_ac_number = $json_bnef->recipientList->dmtRecipient->bankAccountNumber;
                    $update->bank_name = $json_bnef->recipientList->dmtRecipient->bankName;
                    $update->ben_ac_ifsc = $json_bnef->recipientList->dmtRecipient->ifsc;
                    $update->sender_name = $json_sender->senderName;
                    $update->save();
                    
                    $data = transactions_dmt::select('transactions_dmts.*',
                    'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                    ->where('transactions_dmts.id',$ins->id)
                    ->orderBy('transactions_dmts.id','DESC')->first();
                    
                    // return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
            }
            }
            skip:
            $summary = transactions_dmt::select(DB::raw('(SELECT transaction_id FROM transactions_dmts ORDER BY transaction_id ASC LIMIT 1) as transaction_id'),
                        'transactions_dmts.sender_name as customer_name','transactions_dmts.mobile as customer_mobile','transactions_dmts.bank_name')
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN amount ELSE 0 END) as amount'))
                        ->addSelect(DB::raw('SUM(CASE WHEN status IN (0, 1) THEN fee ELSE 0 END) as fee'))
                        ->addSelect(DB::raw('CASE 
                            WHEN SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) > 0 
                                THEN 0
                            WHEN SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) > 0 
                                THEN 1
                            ELSE 2 
                            END as status'))
                        ->where('multi_transaction_id','LIKE','%'.$txnid.'%')
                        ->groupBy('transactions_dmts.sender_name', 'transactions_dmts.mobile', 'transactions_dmts.bank_name')
                        ->first();
            if($summary){
                
            
                if($summary->status == 1 || $summary->status == 0){
                    return response()->json(['success' => true, 'message' => 'Transaction Procced.', 'data'=>$data]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Transaction Failed.', 'data'=>$data]);
                }
                
            }else{
                return response()->json(['success' => false, 'message' => 'Insufficient balance.Please Check Report.']);
            }
        }else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    
}
