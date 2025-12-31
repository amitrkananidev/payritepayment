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
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-locale="en-US" data-pagination="true" data-toggle="table" data-export-types="['csv', 'excel', 'pdf']" data-show-export="true" data-sortable="true" data-url="{{ route('view_retailer_data_distributor') }}" >
                        <thead>
                          <tr>
                            <th data-field="user_name">Name</th>
                            <th data-field="email">Email</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="wallet.balance" data-formatter="priceFormatter">Wallet</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <th data-field="mobile" data-formatter="odModelbutton">OD</th>
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

@include('new_pages.distributor.retailer.model.kyc_document_model')
@include('new_pages.distributor.retailer.model.od')
@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.js"></script>

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
  
  function odModelbutton(value, row, index) {
      return [
          '<button type="button" class="btn btn-dark" onClick="odModelOpen('+value+')">OD</button>'
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
