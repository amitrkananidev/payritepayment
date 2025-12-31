@extends('new_layouts/app')

@section('title', 'DMT Transactions OTP')

@section('page-style')
<style>

.select2-container {
    z-index: 9999; /* Higher than modal's z-index */
    width: 100%;
}

.select2-dropdown {
    z-index: 9999; /* Ensures dropdown is above other elements */
}

.select2-search--dropdown {
    width: 100% !important; /* Ensures search box takes full width */
}

.select2-results__options {
    max-height: 200px; /* Limits height of dropdown options */
    overflow-y: auto; /* Adds scroll if there are too many options */
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">DMT Transactions OTP</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <div class="col-md-12 col-sm-12 my-1" style="background: white;">
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Name : {{ $customer_name }}</button>
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Mobile: {{ $customer_mobile }}</button>
                        <button type="button" class="btn btn-inverse-danger btn-fw" disable>Monthly Limit : {{ $dmt_limit }}</button>
                        <button type="button" class="btn btn-inverse-primary btn-fw" disable>Monthly Used : {{ $dmt_use }}</button>
                        <button type="button" class="btn btn-inverse-success btn-fw" disable>Monthly Available : {{ $available }}</button>
                    </div>
                    
                    <form class="forms-sample" action="{{ route('ace_post_dmt_transaction_otp_retailer') }}" id="dmt_form" method="post">
                        @csrf
                        <input type="hidden" value="{{ $bank_channel }}" id="bank_channel" name="bank_channel">
                      <input type="hidden" value="{{ $customer_mobile }}" id="customer_mobile" name="customer_mobile" />
                      
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <?php $totalamount = 0;  ?>
                      @foreach($txns as $k => $r)
                      <input type="hidden" name="transaction_id[]" id="transaction_id{{ $k }}" value="{{ $r->transaction_id }}">
                      <input type="hidden" name="OTPReferenceID{{ $r->transaction_id }}" id="OTPReferenceID{{ $r->transaction_id }}" value="{{ $r->otp_reference }}">
                      <div class="form-group row">
                        
                        <div class="col-sm-2" style="display: flex;justify-content: center;align-items: center;">
                             <div class="form-check ">
                            <label class="form-check-label card-title card-title-dash text-white">
                                 ₹{{ $r->amount }}</label>
                             </div>
                        </div>
                        <div class="col-sm-4" style="display: flex;justify-content: center;align-items: center;">
                             <div class="form-check">
                            <label class="form-check-label card-title card-title-dash text-white">
                                 {{ $r->transaction_id }} - {{ $customer_mobile }} - {{ $r->otp_reference }}</label>
                             </div>
                        </div>
                        <div class="col-sm-4">
                             <div class="form-check">
                            <input type="text" class="form-control" name="otp" id="otp{{ $r->transaction_id }}" minlength="6" placeholder="OTP" aria-label="OTP" required="" disabled>
                             </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-check" id="button{{ $r->transaction_id }}">
                                <button type="button" class="button btn btn-primary" onclick="SendOTP('{{ $r->transaction_id }}')" style="width: 100%;"><span>SEND OTP!</span></button>
                            </div>
                        </div>
                        
                        
                        
                    </div>
                    <?php $totalamount = $r->amount + $totalamount;  $transafer_type = $r->transfer_type; $transaction_id = $r->transaction_id; ?>
                    @endforeach
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <div class="form-check" >
                                <a href="{{ route('bill_dmt_login_retailer') }}" class="button btn btn-default" style="width: 100%;"><span>DMT LOGIN</span></a>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-check" >
                                <a href="{{ route('dmt_report_retailer') }}" class="button btn btn-warning" style="width: 100%;"><span>REPORT</span></a>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check" >
                                <a href="{{ route('dashboard_retailer') }}" class="button btn btn-danger" style="width: 100%;"><span>HOME</span></a>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check hide" id="receipt">
                                <a href="https://user.payritepayment.in/retailer-receipt/dmt/{{ $transaction_id }}" class="button btn btn-success" style="width: 100%;" target="_blank"><span>PRINT RECEIPT</span></a>
                            </div>
                        </div>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    function letsPay(){
        var beneficiaries = $("#beneficiaries").val();
    }
    
    function confirmBox(transaction_id){
        
        var latitude = $("#latitude").val();
        var error = '<b class="text-success">GOOD TO GO!</b>';
        if (latitude === "") {
            var error = '<b class="text-danger">Please Check Your Location Permission!</b>';
        }
        var html = error+'<table class="table"><tbody> <tr><td class="text-start">Transaction Id</td><td class="text-start">'+ transaction_id +'</td></tr></tbody></table>';
        Swal.fire({
          title: "",
          icon: "info",
          html: html,
          showCloseButton: true,
          showCancelButton: true,
          focusConfirm: false,
          confirmButtonText: `
            <i class="fa fa-thumbs-up"></i> Great!
          `,
          confirmButtonAriaLabel: "Thumbs up, great!",
          cancelButtonText: `
            <i class="fa fa-thumbs-down"></i>
          `,
          cancelButtonAriaLabel: "Thumbs down"
        }).then((result) => {
          if (result.isConfirmed) {
            submitform();  // Call your function here
          }
        });
    }
    
    function submitform(){
        loaderShow();
        $("#dmt_form").submit();
    }
    
    function SendOTP(transaction_id){
        loaderShow();
        var latitude = $("#latitude").val();
        var longitude = $("#longitude").val();
        
        if (latitude === "") {
            var error = '<b class="text-danger">Please Check Your Location Permission!</b>';
            Swal.fire({
                title: "Something Wrong!",
                text: "Please Check Your Location Permission!",
                icon: "error"
            });
        }
        
        if(transaction_id != ""){
            $.ajax({
                type: 'post',
                dataType:'json',
                url: "{{ route('bill_post_dmt_transaction_otp_send_retailer') }}",
                data: {"transaction_id" : transaction_id,"latitude":latitude,"longitude":longitude,"_token":"{{ csrf_token() }}"},
                success: function (result) {
                            loaderHide();
                    if(result.success){
                        var html = '<button type="button" class="button btn btn-success" onclick="verifyOTP(&apos;'+transaction_id+'&apos;)" style="width: 100%;"><span>Verify</span></button>';
                        $('#button'+transaction_id).html(html);
                        $('#otp'+transaction_id).prop("disabled", false);
                        Swal.fire({
                            title: "OTP!",
                            text: "OTP SEND ON YOUR MOBILE.",
                            icon: "success"
                        });            
                    }else{
                        Swal.fire({
                            title: "Something Wrong!",
                            text: result.message,
                            icon: "error"
                        });            
                    }
                            
                }
            });
        }else{
            alert("Please Select Beneficiary");
        }
    }
    
    function verifyOTP(transaction_id){
        loaderShow();
        var latitude = $("#latitude").val();
        var longitude = $("#longitude").val();
        var otp = $("#otp"+transaction_id).val();
        var bank_channel = $("#bank_channel").val();
        var customer_mobile = $("#customer_mobile").val();
        
        $('#otp'+transaction_id).prop("disabled", true);
        if(otp != ""){
            $.ajax({
                type: 'post',
                dataType:'json',
                url: "{{ route('bill_post_dmt_transaction_otp_retailer') }}",
                data: {"customer_mobile":customer_mobile,"bank_channel":bank_channel,"otp" : otp,"transaction_id" : transaction_id,"latitude":latitude,"longitude":longitude ,"_token":"{{ csrf_token() }}"},
                success: function (result) {
                            loaderHide();
                    if(result.success){
                        var html = '<button type="button" class="button btn btn-success" style="width: 100%;" disabled><span>PAID</span></button>';
                        $('#button'+transaction_id).html(html);
                        $("#receipt").removeClass("hide");
                        Swal.fire({
                            title: "DONE!",
                            text: result.message,
                            icon: "success"
                        });            
                    }else{
                        $('#otp'+transaction_id).prop("disabled", false);
                        Swal.fire({
                            title: "Something Wrong!",
                            text: result.message,
                            icon: "error"
                        });            
                    }
                            
                }
            });
        }else{
            $("#acc_verify_button").html("Verify");
            alert("Please Enter Account Number And IFSC Code.");
        }
    }

$(document).ready(function() {
    $('.js-example-basic-single-bank').select2({
        dropdownParent: $('#add_benf')
    });
    //$(".js-example-basic-single-bank").select2();
    $('#beneficiaries').next('.select2-container').css('z-index','0');
    $('#banks').next('.select2-container').css('width','100%');
    
    $('#banks').on('select2:select', function(e) {
                // Get the selected option
        var selectedOption = e.params.data.element;
                
                // Retrieve the data-ifsc attribute
        var ifscCode = $(selectedOption).data('ifsc');

                // Display the IFSC code
        $('#ifsc').val(ifscCode);
    });
});

const x = document.getElementById("demo");

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}

function showPosition(position) {
  
  $("#latitude").val(position.coords.latitude);
  $("#longitude").val(position.coords.longitude);
  $("#accuracy").val(position.coords.accuracy);
}

getLocation();
</script>
@endsection
