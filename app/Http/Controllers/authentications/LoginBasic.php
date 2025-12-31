<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }
  
  public function login(Request $request){
      
        $credentials = $request->only('mobile', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            
            if(Auth::user()->user_type == 1){
                
                return redirect()->intended('/');
            }elseif(Auth::user()->user_type == 3){
                
                return redirect()->intended('/distributor/dashboard');
                
            }elseif(Auth::user()->user_type == 2){
                
                return redirect()->intended('/retailer/dashboard');
                
            }else{
                Auth::logout();
                $request->session()->invalidate();
                return redirect('/');
            }
            
        }

        return redirect()->back()->withErrors(['mobile' => 'Invalid mobile number or password']);
    }
    
  
  public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }
}
