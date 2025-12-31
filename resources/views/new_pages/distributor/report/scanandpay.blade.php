@extends('new_layouts/app')

@section('title', 'Scan And Pay report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Scan And Pay report</h4>
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
                                data-export-options='{"fileName": "ScanNPay_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'
                                data-export-data-type="all"
                                data-url="{{ route('scannpay_report_data_distributor') }}" >
                        <thead>
                          <tr>
                            <th data-field="retailer_name">Retailer</th>
                            <th data-field="retailer_mobile">Retailer Mobile</th>
                            <th data-field="shop_name">Shop</th>
                            <th data-field="transaction_id">Trasnaction Id</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="fee">Fee</th>
                            <th data-field="ben_name" >A/C Holder</th>
                            <th data-field="ben_ac_number" >UPI</th>
                            <th data-field="utr" >UTR</th>
                            <th data-field="created_at">Date</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
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
                        url: '{{ route('scannpay_report_data_distributor') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
function statuColunm(value, row, index) {
      if(value == 1){
        return [
          '<label class="badge badge-success">SUccess</label>'
        ].join('')
      }else if(value == 2 || value == 3){
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
