@extends('new_layouts/app')

@section('title', 'View Retailer')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
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
    max-height: 250px; /* Limits height of dropdown options */
    overflow-y: auto; /* Adds scroll if there are too many options */
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Retailers</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="js-example-basic-single w-100" id="dist_id" name="dist_id" required="">
                                   <option value="0">Select Distributor</option>
                                   @foreach($dist as $r)
                                   <option value="{{ $r->id }}">{{ $r->name }} - {{ $r->mobile }}</option>
                                   @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="filterBtn" class="btn btn-primary">Filter</button>
                            </div>
                            <div class="col-md-3">
                                <a href="https://user.payritepayment.in/retailer/view-data-export?dist_id=0" id="exportBtn" class="btn btn-success">Export CSV</a>
                            </div>
                        </div>
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-locale="en-US" data-pagination="true" data-page-list="[10, 25, 50, 100,500]" data-toggle="table" data-export-types="['csv', 'excel', 'pdf']" data-show-export="true" data-sortable="true" data-url="{{ route('view_retailer_data') }}" >
                        <thead>
                          <tr>
                            <th data-field="user_name">Name</th>
                            <th data-field="user_id">USER ID</th>
                            <th data-field="user_id" data-formatter="userTypeColunm">User Type</th>
                            <th data-field="email">Email</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="dob">DOB</th>
                            <th data-field="kyc_docs.pan_number">PAN</th>
                            <th data-field="kyc_docs.aadhaar_number">AADHAR</th>
                            <th data-field="addresses.address">Address</th>
                            
                            <th data-field="kyc_docs.pan_image" data-formatter="imgUrl">PAN Image</th>
                            <th data-field="kyc_docs.aadhaar_front_image" data-formatter="imgUrl">Aadhaar Front</th>
                            <th data-field="kyc_docs.aadhaar_back_image" data-formatter="imgUrl">Aadhaar Back</th>
                            <th data-field="kyc_docs.cheque_image" data-formatter="imgUrl">Cheque Image</th>
                            <th data-field="kyc_docs.cheque_number" >ChequeNumber</th>
                            <th data-field="kyc_docs.bank_account" >BANK A/C</th>
                            <th data-field="kyc_docs.bank_ifsc" >IFSC</th>
                            <th data-field="shop_detail.shop_img" data-formatter="imgUrlShop">Shop Image</th>
                            <th data-field="shop_detail.latitude" >latitude</th>
                            <th data-field="shop_detail.longitude" >longitude</th>
                            
                            
                            
                            <th data-field="wallet.balance" data-formatter="priceFormatter">Wallet</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <th data-field="mobile" data-formatter="statmentbutton">Statment</th>
                            <th data-field="mobile" data-formatter="odModelbutton">Fund Transfer</th>
                            <th data-field="mobile" data-formatter="resetpassword">Reset Password</th>
                            <th data-field="mobile" data-formatter="kycDocuments">KYC Document</th>
                            
                          </tr>
                        </thead>
                        
                      </table>
                    </div>
                  </div>
                </div>
              </div>
    </div>
</div>

@include('new_pages.admin.retailer.model.kyc_document_model')
@include('new_pages.admin.retailer.model.od')

@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/extensions/export/bootstrap-table-export.min.js"></script>

<script>
$(function() {
            $('#table').bootstrapTable();
            
            
            // Filter button click event
            $('#filterBtn').on('click', function() {
                loaderShow();
                var dist_id = $('#dist_id').val();
                var url = 'https://user.payritepayment.in/retailer/view-data-export?dist_id=' + dist_id;
                $("#exportBtn").attr('href',url);
                
                if (dist_id) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('view_retailer_data') }}?dist_id=' + dist_id,
                        silent: true
                    });
                    
                } else {
                    alert('Please select both start and end dates.');
                }
                loaderHide();
            });
        });

function imgUrl(value, row) {
    return 'https://user.payritepayment.in/uploads/kycdocs/'+value;
} 
function imgUrlShop(value, row) {
    return 'https://user.payritepayment.in/uploads/shop/'+value;
} 

function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
  
  function userTypeColunm(value, row, index) {
      return 'Retailer';
  }
function statuColunm(value, row, index) {
      if(value == 1){
          
      
    return [
      '<label class="badge badge-success">Active</label>'
      
      
    ].join('')
      }else{
    return [
      '<label class="badge btn-inverse-warning">Not Active</label>'
    ].join('')
      }
  }
  
  function kycDocuments(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="getKycDocument('+value+')">Documents</button>'
        ].join('')
  }
  
  function getKycDocument(mobile) {
      loaderShow();
      $("#kycModal").modal("toggle");
      
    $.ajax({
        type: 'post',
        dataType:'html',
        url: "{{ route('post_kyc_document_distributor') }}",
        data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $("#kycdocumentbody").html(result);
        }
    });
    loaderHide();
  }

  function resetpassword(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="sendpassword('+value+')">Send Password</button>'
        ].join('')
  }
  
  function sendpassword(mobile) {
      
      loaderShow();
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('post_send_password') }}",
        data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            loaderHide();
            if(result.success){
                Swal.fire({
                  title: 'Success!',
                  text: result.message,
                  icon: 'success'
                });
            }else{
                Swal.fire({
                  title: 'Error!',
                  text: result.message,
                  icon: 'error'
                });
            }
        }
    });
  }
  
  function statmentbutton(value, row, index) {
      return [
          '<a href="https://user.payritepayment.in/report/user-statment/'+value+'" class="btn btn-light" target="_blank">Statment</a>'
        ].join('')
  }
  
  function odModelbutton(value, row, index) {
      return [
          '<button type="button" class="btn btn-dark" onClick="odModelOpen('+value+')">Fund Transfer</button>'
        ].join('')
  }
  
  function odModelOpen(mobile) {
      $("#odModel").modal("toggle");
      $("#retailer_mobile_od").val(mobile);
  }
  
  $(document).ready(function() {
    $('.js-example-basic-single').select2({
        dropdownParent: $('#odModel')
    });
    
});
</script>
@endsection
