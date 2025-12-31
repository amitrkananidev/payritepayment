@extends('new_layouts/app')

@section('title', 'Agreement OTP')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Agreement OTP</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_create_retailer_otp_distributor') }}" method="post">
                        @csrf
                      <input type="hidden" name="mobile" id="mobile" value="{{ $mobile }}" >
                      <div class="form-group">
                        <label for="name">OTP</label>
                        <input type="text" class="form-control" name="otp" id="otp" placeholder="OTP" value="" required="">
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
