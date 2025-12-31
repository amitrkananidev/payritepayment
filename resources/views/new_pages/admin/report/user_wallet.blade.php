@extends('new_layouts/app')

@section('title', 'User Wallet report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">User Wallet report</h4>
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
                            data-url="{{ route('all_users_wallet_data') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "TDS_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'>
                        <thead>
                          <tr>
                            <th data-field="user_name">Name</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="total_deposits" data-sortable="true">deposits</th>
                            <th data-field="total_withdrawals" data-sortable="true">withdrawals</th>
                            <th data-field="opening_balance" data-sortable="true">Opning Balance</th>
                            <th data-field="closing_balance" data-sortable="true">Clossing Balance</th>
                            <th data-field="current_balance" data-sortable="true">Current Balance</th>
                            <th data-field="opening_balance" data-formatter="calculateDif" data-sortable="true">Diff</th>
                            
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
                        url: '{{ route('all_users_wallet_data') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                    $('#table').bootstrapTable('hideLoading');
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
function calculateDif(value, row) {
    var balanceFloat;
    if(row.total_deposits == 0 && row.total_withdrawals == 0){
        balanceFloat = 0
    }else{
        balanceFloat = parseFloat(value + row.total_deposits + row.total_withdrawals - row.closing_balance);
    }
    return balanceFloat.toFixed(2);
  }
</script>
@endsection
