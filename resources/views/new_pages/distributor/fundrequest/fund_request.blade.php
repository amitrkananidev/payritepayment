@extends('new_layouts/app')

@section('title', 'Pending Fund Request')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Pending Fund Request</h4>
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
                      <table id="table" data-search="true" data-locale="en-US" data-toggle="table" data-url="{{ route('fund_request_data_distributor') }}" >
                        <thead>
                          <tr>
                            <th data-field="transaction_id">Transaction ID</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="bank_name">Bank Name</th>
                            <th data-field="bank_ref">UTR</th>
                            <th data-field="created_at">Date</th>
                            <th data-field="deposit_date">Payment Date</th>
                            <th data-field="created_at">Request Date</th>
                            <th data-field="remark">User Remark</th>
                            <th data-field="admin_remark">Admin Remark</th>
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
@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.js"></script>

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
                        url: '{{ route('fund_request_data_distributor') }}?start_date=' + startDate + '&end_date=' + endDate,
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
