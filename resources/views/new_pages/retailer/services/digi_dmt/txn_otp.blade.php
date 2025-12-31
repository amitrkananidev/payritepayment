@extends('new_layouts/app')

@section('title', 'DIGIKHATA Transactions OTP')

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
                    <h4 class="card-title text-white">DIGIKHATA Transactions OTP</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <div class="col-md-12 col-sm-12 my-1" style="background: white;">
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Name : {{ $customer_name }}</button>
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Mobile: {{ $customer_mobile }}</button>
                    </div>
                    
                    <form class="forms-sample" action="{{ route('digi_post_dmt_transaction_otp_retailer') }}" id="dmt_form" method="post">
                        @csrf
                        
                      <input type="hidden" value="{{ $customer_mobile }}" id="customer_mobile" name="customer_mobile" />
                      <input type="hidden" value="{{ $dmt_token }}" id="dmt_token" name="dmt_token" />
                      <input type="hidden" name="transaction_id" id="transaction_id" value="{{ $txns->transaction_id }}">
                      
                      
                      <div class="form-group row">
                        
                        <div class="col-sm-2" style="display: flex;justify-content: center;align-items: center;">
                             <div class="form-check ">
                            <label class="form-check-label">
                                 ₹{{ $txns->amount }}</label>
                             </div>
                        </div>
                        <div class="col-sm-4" style="display: flex;justify-content: center;align-items: center;">
                             <div class="form-check">
                            <label class="form-check-label">
                                 {{ $txns->transaction_id }} - {{ $customer_mobile }}</label>
                             </div>
                        </div>
                        <div class="col-sm-4">
                             <div class="form-check">
                            <input type="text" class="form-control" name="otp" id="otp" minlength="6" placeholder="OTP" aria-label="OTP" required="">
                             </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-check">
                            <button type="button" class="button btn btn-primary" onclick="confirmBox()"><span>Pay</span></button>
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
    
    function confirmBox(){
        
        var beneficiaries = $('#beneficiaries option:selected').text();
        var customer_mobile = "{{ $customer_mobile }}";
        var amount = "{{ $txns->amount }}";
        var transafer_type = "{{ $txns->transafer_type }}";
        var latitude = $("#latitude").val();
        var error = '<b class="text-success">GOOD TO GO!</b>';
        if (latitude === "") {
            var error = '<b class="text-danger">Please Check Your Location Permission!</b>';
        }
        var html = error+'<table class="table"><tbody> <tr><td class="text-start">Sender Mobile</td><td class="text-start">'+ customer_mobile +'</td></tr> <tr><td class="text-start">Mode</td><td class="text-start">'+ transafer_type +'</td></tr> <tr class="thead-light text-start"><td><strong>Amount</strong></td><td class="text-start"><strong><u><i>₹ '+ amount +'</i></u></strong> (WALLET : ₹ {{ Auth::user()->wallet->balanceFloat }})</td></tr></tbody></table>';
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
        $("#dmt_form").submit();
    }
    
    function delectBenf(){
        var beneficiaries = $("#beneficiaries").val();
        var mobile = $("#mobile").val();
        
        if(beneficiaries != ""){
            Swal.fire({
              title: "Are you sure?",
              text: "You won't be able to revert this!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, delete it!"
            }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                        type: 'post',
                        dataType:'json',
                        url: "{{ route('bill_post_dmt_benf_delete_retailer') }}",
                        data: {"beneficiaries" : beneficiaries,"mobile":mobile ,"_token":"{{ csrf_token() }}"},
                        success: function (result) {
                            
                            if(result.success){
                                Swal.fire({
                                  title: "Deleted!",
                                  text: result.message,
                                  icon: "success"
                                });
                                $('.js-example-basic-single').select2('destroy');
                                $('#beneficiaries').html(result.data);
                                $(".js-example-basic-single").select2();
                            }else{
                                Swal.fire({
                                  title: "Something Wrong!",
                                  text: result.message,
                                  icon: "error"
                                });
                            }
                            
                        }
                    });
                    
                
              }
            });
        }else{
            alert("Please Select Beneficiary");
        }
    }
    
    function verifyBenf(){
        $("#acc_verify_button").html("Validating!! Please Wait.");
        var benf_acc = $("#number").val();
        var ifsc = $("#ifsc").val();
        
        if(benf_acc != "" && ifsc != ""){
            Swal.fire({
              title: "Are you sure?",
              text: "It Will Charge Rs.4!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, Verify it!"
            }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                        type: 'post',
                        dataType:'json',
                        url: "{{ route('post_dmt_benf_verify_retailer') }}",
                        data: {"account" : benf_acc,"ifsc":ifsc ,"_token":"{{ csrf_token() }}"},
                        success: function (result) {
                            
                            if(result.success){
                                Swal.fire({
                                  title: "Verified!",
                                  text: "Account Verified : "+result.data,
                                  icon: "success"
                                });
                                
                                $('#benf_name').val(result.data);
                                $('#is_verify').val(1);
                                $("#acc_verify_button").html("Verified");
                            }else{
                                $("#acc_verify_button").html("Verify");
                                Swal.fire({
                                  title: "Something Wrong!",
                                  text: result.message,
                                  icon: "error"
                                });
                            }
                            
                        }
                    });
                    
                
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
