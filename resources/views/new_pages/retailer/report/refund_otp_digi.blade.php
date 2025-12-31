@extends('new_layouts/app')

@section('title', 'Refund OTP')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">REFUND OTP</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('digi_post_dmt_refund_otp_verify_retailer') }}" method="post">
                        @csrf
                      <input type="hidden" name="txnid" id="txnid" value="{{ $txn->transaction_id }}" >
                      <div class="form-group">
                        <label for="name" class="text-white">OTP</label>
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
