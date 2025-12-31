@extends('new_layouts/app')

@section('title', 'Commission & Fee report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Commission & Fee report</h4>
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
                                <select class="js-example-basic-single w-100" id="users" name="users" required="">
                                   <option value="2">Retailer</option>
                                   <option value="3">Distributor</option>
                                   <option value="ALL">ALL</option>
                                </select>
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
                            data-url="{{ route('commission_fee_data') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "commission_fee", "ignoreColumn": ["operate"], "exportDataType": "all"}'>
                        <thead>
                          <tr>
                            <th data-field="name">Retailer</th>
                            <th data-field="mobile">Retailer Mobile</th>
                            <th data-field="fee_total">Fee</th>
                            <th data-field="dmt1_fee_total">DMT1 Fee</th>
                            <th data-field="dmt2_fee_total">DMT2 Fee</th>
                            <th data-field="upi_fee_total">SCANANDPAY Fee</th>
                            <th data-field="gst_total">GST</th>
                            <th data-field="total_commission_total">Total Commission</th>
                            <th data-field="commission_total">Commission</th>
                            <th data-field="tds_total">TDS</th>
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
                var users = $('#users').val();

                if (startDate && endDate) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('commission_fee_data') }}?start_date=' + startDate + '&end_date=' + endDate + '&users=' + users,
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

</script>
@endsection
