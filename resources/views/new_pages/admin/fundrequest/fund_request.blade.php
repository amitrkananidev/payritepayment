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
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-locale="en-US" data-toggle="table" data-url="{{ route('fund_request_data') }}" >
                        <thead>
                          <tr>
                            <th data-field="status" data-formatter="actionColunm"></th>
                            <th data-field="user_name">Name</th>
                            <th data-field="transaction_id">Transaction ID</th>
                            <th data-field="amount">Amount</th>
                            <th data-field="bank_ref">UTR</th>
                            <th data-field="bank_name">Bank Name</th>
                            <th data-field="deposit_date">Payment Date</th>
                            <th data-field="created_at">Request Date</th>
                            <th data-field="id" data-formatter="bankRecipt">File</th>
                            <th data-field="status" data-formatter="statuColunm">Status</th>
                            
                            <th data-field="remark">Remark</th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.js"></script>

<script>
var $table = $('#table')
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
  
  function actionColunm(value, row, index) {
      if(value == 0){
          
      
    return [
      '<button type="button" class="btn btn-success btn-icon-text" onclick="approveRequest('+row.id+')"><i class="ti-check btn-icon-prepend"></i> Approve </button>',
      '<button type="button" class="btn btn-danger btn-icon-text" onclick="rejectRequest('+row.id+')"><i class="ti-check btn-icon-prepend"></i> Reject </button>'
    ].join('')
      }else{
    return [
      ''
    ].join('')
      }
  }
  
  function myFunction(id) {
        $.ajax({
            url: '{{ route("fund_request_approve") }}', // Replace with your URL
            method: 'POST', // Specify your HTTP method
            data: {
                // Optional: Data to send with the request
                fund_req_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle successful response
                Swal.fire({
                  title: 'Success!',
                  text: response.message,
                  icon: 'success'
                });
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
  
  function approveRequest(id){
      Swal.fire({
          title: "Your Remark!!",
          input: "text",
          inputAttributes: {
            autocapitalize: "off"
          },
          showCancelButton: true,
          confirmButtonText: "Approve",
          showLoaderOnConfirm: true,
          preConfirm: async (login) => {
            try {
              $.ajax({
                    url: '{{ route("fund_request_approve") }}', // Replace with your URL
                    method: 'POST', // Specify your HTTP method
                    data: {
                        // Optional: Data to send with the request
                        fund_req_id: id,
                        remark:login,
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
                        $table.bootstrapTable('refresh');
                        return response;
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
            } catch (error) {
              Swal.showValidationMessage("Request failed: ${error}");
            }
          },
          allowOutsideClick: () => !Swal.isLoading()
        });
  }
  
  function rejectRequest(id){
      Swal.fire({
          title: "Your Remark!!",
          input: "text",
          inputAttributes: {
            autocapitalize: "off"
          },
          showCancelButton: true,
          confirmButtonText: "Reject",
          showLoaderOnConfirm: true,
          preConfirm: async (login) => {
            try {
              $.ajax({
                    url: '{{ route("fund_request_reject") }}', // Replace with your URL
                    method: 'POST', // Specify your HTTP method
                    data: {
                        // Optional: Data to send with the request
                        fund_req_id: id,
                        remark:login,
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
                        $table.bootstrapTable('refresh');
                        return response;
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
            } catch (error) {
              Swal.showValidationMessage("Request failed: ${error}");
            }
          },
          allowOutsideClick: () => !Swal.isLoading()
        });
  }
  
  function myFunctionReject(id) {
        $.ajax({
            url: '{{ route("fund_request_reject") }}', // Replace with your URL
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
</script>
@endsection
