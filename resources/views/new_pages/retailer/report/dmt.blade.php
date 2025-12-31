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
                                <select class="js-example-basic-single w-100" id="dmt_type" name="dmt_type" required="">
                                   <option value="0">DMT 1</option>
                                   <option value="1">DMT 2</option>
                                   <option value="3">DMT 3</option>
                                   <option value="4">DIGIKHATA</option>
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
                            data-url="{{ route('dmt_report_data_retailer') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "DMT_report", "ignoreColumn": ["operate"], "exportDataType": "all"}'>
                        <thead>
                          <tr>
                            <th data-field="retailer_name">Retailer</th>
                            <th data-field="retailer_mobile">Retailer Mobile</th>
                            <th data-field="shop_name">Shop</th>
                            <th data-field="transaction_id">Trasnaction Id</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="fee">Fee</th>
                            <!--<th data-field="gst">GST</th>-->
                            <th data-field="transfer_type">Type</th>
                            <th data-field="customer_name" >Sender Name</th>
                            <th data-field="customer_mobile" >Sender Number</th>
                            <th data-field="ben_name" >A/C Holder</th>
                            <th data-field="ben_ac_number" >A/C No</th>
                            <th data-field="ben_ac_ifsc" >IFSC</th>
                            <th data-field="bank_name" >Bank</th>
                            <th data-field="utr" >UTR</th>
                            <th data-field="created_at">Date</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            <th data-field="transaction_id" data-formatter="kycDocuments">Print</th>
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
                var api_id = $('#dmt_type').val();

                if (startDate && endDate) {
                    $('#table').bootstrapTable('refresh', {
                        url: '{{ route('dmt_report_data_retailer') }}?start_date=' + startDate + '&end_date=' + endDate + '&api_id=' + api_id,
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
      window.open("https://user.payritepayment.in/retailer-receipt/dmt/"+id, "_blank");
  }
</script>
@endsection
