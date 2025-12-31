@extends('new_layouts/app')

@section('title', 'Fund Request')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Fund Request</h4>
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
                            data-url="{{ route('fund_request_report_data') }}" 
                            data-export-data-type="all"
                            data-export-options='{"fileName": "Fund_Request_report", "ignoreColumn": ["operate"], "exportDataType": "all"}' >
                        <thead>
                          <tr>
                            <th data-field="user_name">Name</th>
                            <th data-field="transaction_id">Transaction ID</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="bank_ref">UTR</th>
                            <th data-field="bank_name">Bank Name</th>
                            <th data-field="deposit_date">Payment Date</th>
                            <th data-field="created_at">Request Date</th>
                            <th data-field="remark">User Remark</th>
                            <th data-field="admin_remark" data-formatter="adminRemark">Admin Remark</th>
                            <th data-field="id" data-formatter="bankRecipt">File</th>
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
                        url: '{{ route('fund_request_report_data') }}?start_date=' + startDate + '&end_date=' + endDate,
                        silent: true
                    });
                } else {
                    alert('Please select both start and end dates.');
                }
            });
        });
var $table = $('#table')
  function adminRemark(value, row, index){
      return "\'" + value;
  }
  
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
  
  function actionColunm(value, row, index) {
      if(value == 0){
          
      
    return [
      '<button type="button" class="btn btn-success btn-icon-text" onclick="myFunction('+row.id+')"><i class="ti-check btn-icon-prepend"></i> Approve </button>',
      '<button type="button" class="btn btn-danger btn-icon-text" onclick="myFunctionReject('+row.id+')"><i class="ti-check btn-icon-prepend"></i> Reject </button>'
    ].join('')
      }else{
    return [
      ''
    ].join('')
      }
  }
  
  function myFunction(id) {
        $.ajax({
            url: '{{ route("fund_request_approve_distributor") }}', // Replace with your URL
            method: 'POST', // Specify your HTTP method
            data: {
                // Optional: Data to send with the request
                fund_req_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle successful response
                if(response.success){
                    Swal.fire({
                      title: 'Success!',
                      text: response.message,
                      icon: 'success'
                    });
                }else{
                    Swal.fire({
                      title: 'Error!',
                      text: response.message,
                      icon: 'error'
                    });
                }
                
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
  
  function myFunctionReject(id) {
        $.ajax({
            url: '{{ route("fund_request_reject_distributor") }}', // Replace with your URL
            method: 'POST', // Specify your HTTP method
            data: {
                // Optional: Data to send with the request
                fund_req_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle successful response
                if(response.success){
                    Swal.fire({
                      title: 'Success!',
                      text: response.message,
                      icon: 'success'
                    });
                }else{
                    Swal.fire({
                      title: 'Error!',
                      text: response.message,
                      icon: 'error'
                    });
                }
                
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
  
  function bankRecipt(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="getBankRec('+value+')">Documents</button>'
        ].join('')
  }
  
  function getBankRec(mobile) {
      $("#BankRecModal").modal("toggle");
      
    $.ajax({
        type: 'post',
        dataType:'html',
        url: "{{ route('post_bank_rec') }}",
        data: {"fund_id" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $("#BankRecbody").html(result);
        }
    });
  }
</script>
@endsection
