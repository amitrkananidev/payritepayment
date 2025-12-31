@extends('new_layouts/app')

@section('title', 'My Statment')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">My Statment</h4>
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
                      <table id="table" data-pagination="true"
                                        data-page-list="[10, 25, 50, 100, ALL]"
                                        data-search="true" 
                                        data-locale="en-US"
                                        data-toggle="table"
                                        data-export-types="['csv', 'excel', 'pdf']" 
                                        data-show-export="true" 
                                        data-export-options='{"fileName": "Statment", "ignoreColumn": ["operate"], "exportDataType": "all"}'
                                        data-export-data-type="all"
                                        data-url="{{ route('my_statment_data_retailer') }}" >
                        <thead>
                          <tr>
                            
                            <th data-field="meta.meta.transaction_id">Transaction ID</th>
                            <th data-field="meta.meta.detail">Detail</th>
                            <th data-field="amount" data-formatter="priceFormatter">Amount</th>
                            <th data-field="type">Type</th>
                            <th data-field="balance">Closing</th>
                            <th data-field="created_at">Date</th>
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
                        url: '{{ route('my_statment_data_retailer') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
var $table = $('#table')
  function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    
    return balanceFloat;
  }
  function statuColunm(value, row, index) {
      if(value == 0){
          
      
    return [
      
      '<label class="badge btn-inverse-warning">In progress</label>'
      
    ].join('')
      }else{
    return [
      '<label class="badge badge-success">Approved</label>'
    ].join('')
      }
  }
  
  
</script>
@endsection
