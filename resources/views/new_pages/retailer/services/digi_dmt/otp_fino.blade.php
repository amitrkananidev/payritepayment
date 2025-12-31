@extends('new_layouts/app')

@section('title', 'DIGIKHATA Login')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">OTP</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" id="scandata" action="{{ route('digi_post_dmt_create_customer_verify_retailer') }}" method="post">
                        @csrf
                        <input type="hidden" value="{{ $dmt_token }}" id="dmt_token" name="dmt_token">
                        <input type="hidden" value="{{ $otp_token }}" id="otp_token" name="otp_token">
                        
                      
                      <input type="hidden" name="mobile" id="mobile" value="{{ $customer_mobile }}" >
                      <input type="hidden" name="name" id="name" value="{{ $name }}" >
                      <input type="hidden" name="pincode" id="pincode" value="{{ $pincode }}" >
                      
                      <div class="form-group">
                        <label for="name" class="text-white">OTP</label>
                        <input type="text" class="form-control" name="otp" id="otp" placeholder="OTP" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Pan Number</label>
                        <input type="text" class="form-control" name="pan_number" id="pan_number" placeholder="Pan Number" value="" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Next</span></button>
                      </div>
                      
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>

@endsection

@section('page-script')

@endsection
