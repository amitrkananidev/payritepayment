@extends('new_layouts/app')

@section('title', 'CCPAYOUT report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">CCPAYOUT report</h4>
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
                                <button type="button" id="filterBtn" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-pagination="true" 
                                data-toggle="table" 
                                data-locale="en-US"
                                data-export-types="['csv', 'excel', 'pdf']" 
                                data-show-export="true" 
                                data-sortable="true" 
                                data-export-options='{"fileName": "CC_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'
                                data-export-data-type="all"
                                data-url="{{ route('ccpayout_report_data_retailer') }}" >
                        <thead>
                          <tr>
                            <th data-field="transaction_id">Trasnaction Id</th>
                            <th data-field="amount" data-formatter="totalAmount">Amount</th>
                            <th data-field="fee">Fee</th>
                            <th data-field="gst">GST</th>
                            <th data-field="senders.name_in_pan">Sender Name</th>
                            <th data-field="senders.mobile">Sender Mobile</th>
                            <th data-field="beneficiaries.account_holder_name">Beneficiary Name</th>
                            <th data-field="beneficiaries.account_number">Beneficiary Account</th>
                            <th data-field="beneficiaries.ifsc">IFSC</th>
                            <th data-field="transfer_type" data-formatter="evenColunm">Transfer Type</th>
                            <th data-field="senders.card_type">Card Type</th>
                            <th data-field="senders.card_number" data-formatter="aadharColunm">Card</th>
                            <th data-field="utr" >UTR</th>
                            <th data-field="created_at">Date</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <!--<th data-field="transaction_id" data-formatter="kycDocuments">Receipt</th>-->
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
$(function() {
            $('#table').bootstrapTable();
            
            var today = new Date().toISOString().split('T')[0];
            $('#startDate').val(today);
            $('#endDate').val(today);
            // Filter button click event
            $('#filterBtn').on('click', function() {
                
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();

                if (startDate && endDate) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('ccpayout_report_data_retailer') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });

function totalAmount(value, row) {
    var total = value + row.fee;
    return total;
  }
  
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
function aadharColunm(value, row, index) {
    return value.slice(0, -4).replace(/\d/g, 'X') + value.slice(-4);
}
function evenColunm(value, row, index) {
    if(value == "1"){
        return [
          '<label class="badge btn-inverse-info">T+0</label>'
        ].join('')
    }else if(value == '2'){
          return [
          '<label class="badge btn-inverse-warning">T+1</label>'
        ].join('')
    }else{
        return [
          '<label class="badge badge-success">/</label>'
        ].join('')
    }
}
function statuColunm(value, row, index) {
      if(value == 1){
        return [
          '<label class="badge badge-success">Success</label>'
        ].join('')
      }else if(value == 2){
          return [
          '<label class="badge btn-inverse-danger">Failed</label>'
        ].join('')
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
      window.open("https://user.payritepayment.in/retailer-receipt/dmt/"+id, "_blank");
  }
</script>
@endsection
