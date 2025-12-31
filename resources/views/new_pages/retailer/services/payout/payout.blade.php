@extends('new_layouts/app')

@section('title', 'Payout')

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
                    <h4 class="card-title text-white">Payout</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="" method="post">
                        @csrf
                      <input type="hidden" value="" id="mobile" name="mobile" />
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <div class="form-group row">
                          <div class="col-md-9 col-sm-12">
                           
                           <select class="js-example-basic-single w-100 form-control" id="beneficiaries" name="beneficiaries" onchange="letsPay()" required="">
                               <option value="">Select Beneficiaries</option>
                              
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
@include('new_pages.retailer.services.dmt.add_benf')
@endsection

@section('page-script')
<script>
 
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
