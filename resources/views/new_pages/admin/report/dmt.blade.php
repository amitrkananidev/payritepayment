@extends('new_layouts/app')

@section('title', 'DMT report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">DMT report</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" id="startDate" class="form-control" placeholder="Start Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="endDate" class="form-control" placeholder="End Date">
                            </div>
                            <div class="col-md-3">
                                <select class="js-example-basic-single w-100" id="event" name="event" required="">
                                   <option value="DMT">DMT</option>
                                   <option value="SCANNPAY">Scan And Pay</option>
                                   <option value="ALL">ALL</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="js-example-basic-single w-100" id="api_id" name="api_id" required="">
                                   <option value="0">DMT 1 (BP)</option>
                                   <option value="1">DMT 2 (BA)</option>
                                   <option value="3">DMT 3 (ACE)</option>
                                   <option value="4">DIGIKHATA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="filterBtn" class="btn btn-primary">Filter</button>
                            </div>
                            <div class="col-md-3">
                                <a href="https://user.payritepayment.in/report/dmt-data-export?" id="exportBtn" class="btn btn-primary">Export CSV</a>
                            </div>
                        </div>
                    <div class="table-responsive">
                      <table id="table" data-search="true" 
                            data-pagination="true" 
                            data-locale="en-US"
                            data-toggle="table" 
                            data-export-types="['csv', 'excel', 'pdf']" 
                            data-show-export="true" 
                            data-sortable="true" 
                            data-url="{{ route('dmt_report_data') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "DMT_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'>
                        <thead>
                          <tr>
                            <th data-field="dist_name">Distributor</th>
                            <th data-field="dist_mobile">Distributor Mobile</th>
                            <th data-field="retailer_name">Retailer</th>
                            <th data-field="retailer_mobile">Retailer Mobile</th>
                            <th data-field="shop_name">Shop</th>
                            <th data-field="transaction_id">Trasnaction Id</th>
                            <th data-field="multi_transaction_id" data-formatter="multiTxnId">Multi Txn Id</th>
                            <th data-field="multi_transaction_id" data-formatter="multiTxn">Multi Txn Order</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="fee">Fee</th>
                            <th data-field="gst">GST</th>
                            <th data-field="transfer_type">Type</th>
                            <th data-field="customer_name" >Sender Name</th>
                            <th data-field="customer_mobile" >Sender Number</th>
                            <th data-field="ben_name" >A/C Holder</th>
                            <th data-field="ben_ac_number" >A/C No</th>
                            <th data-field="ben_ac_ifsc" >IFSC</th>
                            <th data-field="bank_name" >Bank</th>
                            <th data-field="utr" >UTR</th>
                            <th data-field="tid" >#</th>
                            <th data-field="created_at">Date</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <th data-field="transaction_id" data-formatter="failedTxn">/</th>
                            <th data-field="transaction_id" data-formatter="successTxn">/</th>
                            <th data-field="transaction_id" data-formatter="refundProccess">/</th>
                          </tr>
                        </thead>
                        
                      </table>
                    </div>
                  </div>
                </div>
              </div>
    </div>
</div>


@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/extensions/export/bootstrap-table-export.min.js"></script>

<script>
var $table = $('#table')
        $(function() {
            $('#table').bootstrapTable();
            
            var today = new Date().toISOString().split('T')[0];
            $('#startDate').val(today);
            $('#endDate').val(today);
            // Filter button click event
            $('#filterBtn').on('click', function() {
                
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var event = $('#event').val();
                var api_id = $('#api_id').val();
                var url = 'https://user.payritepayment.in/report/dmt-data-export?start_date=' + startDate + '&end_date=' + endDate + '&event=' + event + '&api_id=' + api_id;
                $("#exportBtn").attr('href',url);

                if (startDate && endDate) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('dmt_report_data') }}?start_date=' + startDate + '&end_date=' + endDate + '&event=' + event + '&api_id=' + api_id,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
function multiTxn(value, row) {
    if (typeof value !== 'undefined' && value !== null) {
        const myArray = value.split("#");
        return myArray[1];
    }else{
        return "-";
    }
}
function multiTxnId(value, row) {
    if (typeof value !== 'undefined' && value !== null) {
        const myArray = value.split("#");
        return myArray[0];
    }else{
        return "-";
    }
}
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
function statuColunm(value, row, index) {
      if(value == 1){
        return [
          '<label class="badge badge-success">Success</label>'
        ].join('')
      }else if(value == 2 || value == 3){
          return [
          '<label class="badge btn-inverse-danger">Failed</label>'
        ].join('')
      }else if(value == 0 && row.eko_status == 3){
        if(row.api_id == 4){
            return [
          '<a href="https://user.payritepayment.in/retailer-services/dmt4/refund-otp/'+row.transaction_id+'" class="badge btn-inverse-info">refund Pending</a>'
        ].join('')
        }else{
            return [
          '<a href="https://user.payritepayment.in/retailer-services/bill-dmt/refund-otp/'+row.transaction_id+'" class="badge btn-inverse-info">refund Pending</a>'
        ].join('')
        }
        
      }else{
        return [
          '<label class="badge btn-inverse-warning">Pending</label>'
        ].join('')
      }
  }
  
  function kycDocuments(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="getKycDocument(&apos;'+value+'&apos;)">Receipt</button>'
        ].join('')
  }
  
  function getKycDocument(id) {
      window.open("https://user.payritepayment.in/receipt/dmt/"+id, "_blank");
  }
  
  function failedTxn(value, row, index) {
      if(row.status == 0){
          return [
          '<button type="button" class="btn btn-danger" onClick="makeFailed(&apos;'+value+'&apos;)">Fail</button>'
        ].join('')
      }else{
          return "No change";
      }
      
  }
  
  function makeFailed(id) {
        $.ajax({
            url: '{{ route("dmt_txn_failed") }}', // Replace with your URL
            method: 'POST', // Specify your HTTP method
            data: {
                // Optional: Data to send with the request
                transaction_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle successful response
                Swal.fire({
                  title: 'Success!',
                  text: response.message,
                  icon: 'success'
                });
                $table.bootstrapTable('refresh')
            },
            error: function(xhr, status, error) {
                // Handle errors
                Swal.fire({
                  title: 'Error!',
                  text: 'Somwthing Want Wrong',
                  icon: 'error'
                });
            }
        });
  }
  
  function successTxn(value, row, index) {
      if(row.status == 0){
          return [
          '<button type="button" class="btn btn-success" onClick="makeSuccess(&apos;'+value+'&apos;)">Success</button>'
        ].join('')
      }else{
          return "No change";
      }
      
  }
  
  function refundProccess(value, row, index) {
      if(row.status == 0  && row.api_id == 1){
          return [
          '<a target="_blank" class="btn btn-success" href="https://user.payritepayment.in/report/refund-otp/'+value+'">Refund</a>'
        ].join('')
      }else{
          return "No change";
      }
      
  }
  
  function makeSuccess(id) {
        $.ajax({
            url: '{{ route("dmt_txn_success") }}', // Replace with your URL
            method: 'POST', // Specify your HTTP method
            data: {
                // Optional: Data to send with the request
                transaction_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle successful response
                Swal.fire({
                  title: 'Success!',
                  text: response.message,
                  icon: 'success'
                });
                $table.bootstrapTable('refresh')
            },
            error: function(xhr, status, error) {
                // Handle errors
                Swal.fire({
                  title: 'Error!',
                  text: 'Somwthing Want Wrong',
                  icon: 'error'
                });
            }
        });
  }
</script>
@endsection
