@extends('new_layouts/app')

@section('title', 'DMT Beneficiaries')

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
                    <h4 class="card-title text-white">DMT Beneficiaries</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <div class="col-md-12 col-sm-12 my-1" style="background: white;">
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Name : {{ $customer_name }}</button>
                        <button type="button" class="btn btn-inverse-warning btn-fw" disable>Customer Mobile: {{ $customer_mobile }}</button>
                        <button type="button" class="btn btn-inverse-danger btn-fw" disable>Monthly Limit : {{ $dmt_limit }}</button>
                        <button type="button" class="btn btn-inverse-primary btn-fw" disable>Monthly Used : {{ $dmt_use }}</button>
                        <button type="button" class="btn btn-inverse-success btn-fw" disable>Monthly Available : {{ $available }}</button>
                    </div>
                    
                    <form class="forms-sample" action="{{ route('eko_post_dmt_transaction_retailer') }}" method="post">
                        @csrf
                      <input type="hidden" value="{{ $customer_mobile }}" id="mobile" name="mobile" />
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <div class="form-group row">
                          <div class="col-md-9 col-sm-12">
                           
                           <select class="js-example-basic-single w-100 form-control" id="beneficiaries" name="beneficiaries" onchange="letsPay()" required="">
                               <option value="">Select Beneficiaries</option>
                               @foreach($data as $r)
                                <option value="{{ $r->recipient_id }}">{{ $r->recipient_name }} | {{ $r->account }} | {{ $r->ifsc }} | {{ $r->recipient_mobile }}</option>
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
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" aria-label="Amount">
                          </div>
                        </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Pay</span></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>
@include('new_pages.retailer.services.eko_dmt.add_benf')
@endsection

@section('page-script')
<script>
    function letsPay(){
        var beneficiaries = $("#beneficiaries").val();
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
                        url: "{{ route('post_dmt_benf_delete_retailer') }}",
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
}

getLocation();
</script>
@endsection
