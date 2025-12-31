@extends('new_layouts/app')

@section('title', 'DIGIKHATA Beneficiaries')

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
                    <h4 class="card-title text-white">DIGIKHATA Beneficiaries</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <div class="col-md-12 col-sm-12 my-1" style="background: white;">
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Name : {{ $customer_name }}</button>
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Mobile: {{ $customer_mobile }}</button>
                        <button type="button" class="btn btn-inverse-danger btn-fw" disable>Monthly Limit : {{ $dmt_limit }}</button>
                        <button type="button" class="btn btn-inverse-primary btn-fw" disable>Monthly Used : {{ $dmt_use }}</button>
                        <button type="button" class="btn btn-inverse-success btn-fw" disable>Monthly Available : {{ $available }}</button>
                    </div>
                    
                    <form class="forms-sample" action="{{ route('digi_post_dmt_transaction_retailer') }}" id="dmt_form" method="post">
                        @csrf
                        <input type="hidden" value="{{ $dmt_token }}" id="dmt_token" name="dmt_token">
                      <input type="hidden" value="{{ $customer_mobile }}" id="mobile" name="mobile" />
                      <input type="hidden" value="{{ $customer_name }}" id="sender_name" name="sender_name" />
                      
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <input type="hidden" name="accuracy" id="accuracy" value="">
                      <div class="form-group row">
                          <div class="col-md-9 col-sm-12">
                           
                           <select class="js-example-basic-single w-100 form-control" id="beneficiaries" name="beneficiaries" onchange="letsPay()" required="">
                               <option value="">Select Beneficiaries</option>
                               @foreach($data as $r)
                                @if (is_string($r->beneficiaryMobile))
                                <?php $mobileno = $r->beneficiaryMobile;  ?>
                                @else
                                <?php $mobileno = '';  ?>
                                @endif
                                
                                @if (is_string($r->beneficiaryName))
                                    <option value="{{ $r->beneId }}#{{ $r->beneficiaryName }}#{{ $r->accountNo }}#{{ $r->ifsCcode }}">{{ $r->beneficiaryName }} | {{ $r->accountNo }} | {{ $r->ifsCcode }} | {{ $mobileno }}</option>
                                @else
                                    <option value="{{ $r->beneId }}#-#{{ $r->accountNo }}#{{ $r->ifsCcode }}">  | {{ $r->accountNo }} | {{ $r->ifsCcode }} | {{ $mobileno }}</option>
                                @endif
                               @endforeach
                           </select>
                           
                        </div>
                        <div class="col-md-1 col-sm-6">
                            <button class="btn btn-danger" type="button" onclick="delectBenf()" style="height: 44px;">
                                <i class="ti-trash"></i>
                            </button>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <button class="btn btn-primary" type="button" style="height: 44px;" data-bs-toggle="modal" data-bs-target="#add_benf">
                                 Add Beneficiary
                            </button>
                        </div>
                      </div>
                      
                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-white">Transaction Mode</label>
                        <div class="col-sm-4">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="transafer_type" id="imps" value="IMPS" checked=""> IMPS <i class="input-helper"></i></label>
                             </div>
                        </div>
                        <div class="col-sm-5">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="transafer_type" id="neft" value="NEFT"> NEFT <i class="input-helper"></i></label>
                             </div>
                        </div>
                    </div>
                        <div class="form-group">
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text">₹</span>
                            </div>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" aria-label="Amount" required="">
                          </div>
                        </div>
                      
                      <div class="button-container">
                        <button type="button" class="button btn btn-primary" onclick="confirmBox()"><span>Pay</span></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>
@include('new_pages.retailer.services.digi_dmt.add_benf')
@endsection

@section('page-script')
<script>
    function letsPay(){
        var beneficiaries = $("#beneficiaries").val();
    }
    
    function confirmBox(){
        
        var beneficiaries = $('#beneficiaries option:selected').text();
        var details = beneficiaries.split('|');
        var amount = $("#amount").val();
        var transafer_type = $('input[name="transafer_type"]:checked').val();
        var latitude = $("#latitude").val();
        var error = '<b class="text-success">GOOD TO GO!</b>';
        if (latitude === "") {
            var error = '<b class="text-danger">Please Check Your Location Permission!</b>';
        }
        var html = error+'<table class="table"><tbody><tr><td class="text-start">A/C Holder</td><td class="text-start">'+ details[0] +'</td></tr> <tr><td class="text-start">A/C No.</td><td class="text-start">'+ details[1] +'</td></tr> <tr><td class="text-start">IFSC</td><td class="text-start">'+ details[2] +'</td></tr> <tr><td class="text-start">Mobile</td><td class="text-start">'+ details[3] +'</td></tr> <tr><td class="text-start">Mode</td><td class="text-start">'+ transafer_type +'</td></tr> <tr class="thead-light text-start"><td><strong>Amount</strong></td><td class="text-start"><strong><u><i>₹ '+ amount +'</i></u></strong> (WALLET : ₹ {{ Auth::user()->wallet->balanceFloat }})</td></tr></tbody></table>';
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
        const myArray = beneficiaries.split("#");
        var mobile = $("#customer_mobile").val();
        var dmt_token = $("#dmt_token_add").val();
        
        if(beneficiaries != ""){
            Swal.fire({
              title: "Are you sure?",
              text: "You won't be able to revert this!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, OTP SEND!"
            }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                        type: 'post',
                        dataType:'json',
                        url: "{{ route('digi_post_dmt_benf_delete_retailer') }}",
                        data: {"ben_id" : myArray[0],"sender_mobile":mobile,"dmt_token":dmt_token ,"_token":"{{ csrf_token() }}"},
                        success: function (response) {
                            
                            if(response.success){
                                var otp_token = response.data.data.result.otpToken;
                                
                                Swal.fire({
                                  title: "OTP SEND!",
                                  text: response.message,
                                  input: "text",
                                  icon: "success",
                                  showCancelButton: true,
                                  confirmButtonColor: "#3085d6",
                                  cancelButtonColor: "#d33",
                                  confirmButtonText: "Yes, delete it!"
                                }).then((result2) => {
                                    console.log(result2);
                                  if (result2.isConfirmed) {
                                      const otp = result2.value;
                                      
                                      $.ajax({
                                            type: 'post',
                                            dataType:'json',
                                            url: "{{ route('digi_post_dmt_benf_delete_otp_retailer') }}",
                                            data: {"otp":otp,"otp_token" : otp_token,"sender_mobile":mobile,"dmt_token":dmt_token ,"_token":"{{ csrf_token() }}"},
                                            success: function (response) {
                                                
                                                if(response.success){
                                                    Swal.fire({
                                                      title: "DELETED!",
                                                      text: response.message,
                                                      icon: "success"
                                                    });
                                                    $('.js-example-basic-single').select2('destroy');
                                                    $('#beneficiaries').html(response.data);
                                                    $(".js-example-basic-single").select2();
                                                }else{
                                                    Swal.fire({
                                                      title: "Something Wrong!",
                                                      text: response.message,
                                                      icon: "error"
                                                    });
                                                }
                                                
                                            }
                                        });
                                        
                                    
                                  }
                                });
                                
                            }else{
                                Swal.fire({
                                  title: "Something Wrong!",
                                  text: response.message,
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
    
    function addBenf(){
        var dmt_token = $("#dmt_token_add").val();
        var customer_mobile = $("#customer_mobile").val();
        var benf_name = $("#benf_name").val();
        var number = $("#number").val();
        var banks = $("#banks").val();
        var ifsc = $("#ifsc").val();
        var benf_mobile = $("#benf_mobile").val();
        
        if(beneficiaries != ""){
            Swal.fire({
              title: "Are you sure?",
              text: "You want to add this!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, Add it!"
            }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                        type: 'post',
                        dataType:'json',
                        url: "{{ route('digi_post_dmt_benf_add_retailer') }}",
                        data: {"benf_mobile" : benf_mobile,"banks" : banks,"ifsc" : ifsc,"dmt_token" : dmt_token,"mobile":customer_mobile, "benf_name" : benf_name, "number" : number,"is_verify":0 ,"_token":"{{ csrf_token() }}"},
                        success: function (result) {
                            
                            if(result.success){
                                Swal.fire({
                                  title: "Added!",
                                  text: result.message,
                                  icon: "success"
                                });
                                $('.js-example-basic-single').select2('destroy');
                                $('#beneficiaries').html(result.data);
                                $(".js-example-basic-single").select2();
                                $('#add_benf').modal('hide');
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

function getBrowserLocationAsFallback() {
  navigator.geolocation.getCurrentPosition(
    (position) => {
      console.log("Fallback browser location:", position);
      $("#latitude").val(position.coords.latitude);
      $("#longitude").val(position.coords.longitude);
      $("#accuracy").val(position.coords.accuracy);
    },
    (error) => {
      console.error("Fallback geolocation error:", error);
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  );
}

getBrowserLocationAsFallback();
</script>
@endsection
