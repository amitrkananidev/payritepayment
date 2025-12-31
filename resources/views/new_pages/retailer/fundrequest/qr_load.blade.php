@extends('new_layouts/app')

@section('title', 'QR Report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">QR Report</h4>
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
                      <table id="table" data-search="true" 
                            data-pagination="true" 
                            data-locale="en-US"
                            data-toggle="table" 
                            data-export-types="['csv', 'excel', 'pdf']" 
                            data-show-export="true" 
                            data-sortable="true" 
                            data-url="{{ route('qr_fund_data_retailer') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "qr_fund_data_retailer", "ignoreColumn": ["operate"], "exportDataType": "all"}' >
                        <thead>
                          <tr>
                            <th data-field="transaction_id">Transaction ID</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="amount_txn">Credited Amount</th>
                            <th data-field="fee">Fee</th>
                            <th data-field="payer_name">Payer name</th>
                            <th data-field="cust_mobile">Payer Mobile</th>
                            <th data-field="card_number">VPA ID</th>
                            <th data-field="card_type">APP</th>
                            <th data-field="pg_ref_id">RRN</th>
                            <th data-field="updated_at">Payment Date</th>
                            <th data-field="updated_at">Modify Date</th>
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
@include('new_pages.admin.fundrequest.model.bank_rec_model')
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
                        url: '{{ route('qr_fund_data_retailer') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
var $table = $('#table')
  function statuColunm(value, row, index) {
      if(value == 0){
        return [
          '<label class="badge btn-inverse-warning">In progress</label>'
        ].join('')
      }else if(value == 1){
          return [
              '<label class="badge badge-success">Approved</label>'
            ].join('')
      }else{
            return [
              '<label class="badge badge-danger">Rejected</label>'
            ].join('')
      }
  }
</script>
@endsection
