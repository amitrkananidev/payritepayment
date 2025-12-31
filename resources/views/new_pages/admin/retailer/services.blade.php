@extends('new_layouts/app')

@section('title', 'Services')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.5/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Services</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <div class="table-responsive">
                      <table id="table" data-search="true" data-pagination="true" data-toggle="table" data-export-types="['csv', 'excel', 'pdf']" data-show-export="true" data-sortable="true" data-url="{{ route('services_retailer_data') }}" >
                        <thead>
                          <tr>
                            <th data-field="user_name">Name</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="credopay_cpid">CredoPay Code</th>
                            <th data-field="credopay_aeps"  data-formatter="statuColunm" data-column-name="CredoPay AEPS">CredoPay AEPS</th>
                            <th data-field="airpay_pg_status"  data-formatter="statuColunm" data-column-name="Airpay PG">Airpay PG</th>
                            <th data-field="cyrus_code">Cyrus Code</th>
                            <th data-field="cyrus_aeps"  data-formatter="statuColunm" data-column-name="Cyrus AEPS">Cyrus AEPS</th>
                            <th data-field="cyrus_payout"  data-formatter="statuColunm" data-column-name="Cyrus PAYOUT">Cyrus PAYOUT</th>
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
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
function statuColunm(value, row, index, field) {
    const columnName = row[field + '_columnName'];
      if(value == 1){
      console.log(columnName);
    return [
      '<button type="button" class="btn btn-inverse-success btn-fw '+field+'">Active</button>'
    ].join('')
      }else{
    return [
      '<button type="button" class="btn btn-inverse-warning btn-fw" onclick="activeService(&apos;'+field+'&apos;,1,'+row.user_idd+')">Not Active</button>'
    ].join('')
      }
  }

function activeService(service,status,user_id){
    
    
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('service_active') }}",
        data: {"user_id":user_id,"service" : service, "status" : status ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            if(result.success){
                Swal.fire({
                  title: 'Success!',
                  text: result.message,
                  icon: 'success'
                });
                
                $('#table').bootstrapTable('refresh', {
                        url: '{{ route('services_retailer_data') }}',
                        silent: true
                    });
            }else{
                Swal.fire({
                  title: 'Error!',
                  text: result.message,
                  icon: 'error'
                });
            }
        }
    });
}
</script>
@endsection
