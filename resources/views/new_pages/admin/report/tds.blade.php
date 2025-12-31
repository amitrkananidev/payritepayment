@extends('new_layouts/app')

@section('title', 'TDS report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">TDS report</h4>
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
                            <div class="col-md-3">
                                <a href="https://user.payritepayment.in/report/tds-data-export?" id="exportBtn" class="btn btn-success">Export CSV</a>
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
                            data-url="{{ route('tds_data') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "TDS_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'>
                        <thead>
                          <tr>
                            <th data-field="retailer_name">Name</th>
                            <th data-field="dob">DOB</th>
                            <th data-field="user_type">Type</th>
                            <th data-field="aadhaar_number">Aadhar</th>
                            <th data-field="pan_number">Pan</th>
                            <th data-field="address">Address</th>
                            <th data-field="pincode">Pincode</th>
                            <th data-field="email">Email</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="created_at">TDS Deduct</th>
                            <th data-field="total_amount">Amount</th>
                            <th data-field="tds">TDS</th>
                            <th data-field="tds_par">TDS %</th>
                            
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
                var url = 'https://user.payritepayment.in/report/tds-data-export?start_date=' + startDate + '&end_date=' + endDate;
                $("#exportBtn").attr('href',url);

                if (startDate && endDate) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('tds_data') }}?start_date=' + startDate + '&end_date=' + endDate,
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
    function cgst(value, row, index) {
      return value/2;
    }
</script>
@endsection
