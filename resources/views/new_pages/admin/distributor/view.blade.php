@extends('new_layouts/app')

@section('title', 'View Distributor')

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
                    <h4 class="card-title">Distributor</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-locale="en-US" data-pagination="true" data-toggle="table" data-export-types="['csv', 'excel', 'pdf']" data-show-export="true" data-sortable="true" data-url="{{ route('view_distributor_data') }}" >
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
                            <th data-field="wallet.balance" data-formatter="priceFormatter">Wallet</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <th data-field="mobile" data-formatter="statmentbutton">Statment</th>
                            <th data-field="mobile" data-formatter="odModelbutton">Fund Transfer</th>
                            <th data-field="mobile" data-formatter="resetpassword">Reset Password</th>
                            <th data-field="mobile" data-formatter="kycDocuments">KYC Document</th>
                            <th data-field="mobile" data-formatter="createRetailer">Create</th>
                          </tr>
                        </thead>
                        
                      </table>
                    </div>
                  </div>
                </div>
              </div>
    </div>
</div>

@include('new_pages.admin.distributor.model.kyc_document_model')
@include('new_pages.admin.distributor.model.od')
@include('new_pages.admin.distributor.model.create_retailer')
@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/extensions/export/bootstrap-table-export.min.js"></script>

<script>
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
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
  }
  
  function userTypeColunm(value, row, index) {
      return 'Distributor';
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
  
  function createRetailer(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="postCreateRetailer('+value+')">Create</button>'
        ].join('')
  }
  
  function postCreateRetailer(mobile) {
      $("#CreateRetailerModel").modal("toggle");
      $("#current_mobile").val(mobile);
  }
  
  function resetpassword(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="sendpassword('+value+')">Send Password</button>'
        ].join('')
  }
  
  function sendpassword(mobile) {
      
      
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('post_send_password') }}",
        data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
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
  
  $(document).ready(function() {
    $('.js-example-basic-single').select2({
        dropdownParent: $('#odModel')
    });
    
});
</script>
@endsection
