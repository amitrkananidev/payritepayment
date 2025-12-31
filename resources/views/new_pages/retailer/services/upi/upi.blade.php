@extends('new_layouts/app')

@section('title', 'UPI Transfer')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">UPI Transfer</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_upi_transfer_retailer') }}" method="post">
                        @csrf
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <div class="form-group">
                        <label for="name" class="text-white">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="" required="">
                      </div>
                      <div class="form-group">
                        <label for="name" class="text-white">UPI</label>
                        <input type="text" class="form-control" name="upi" id="upi" placeholder="UPI" value="" required="">
                      </div>
                      <div class="form-group">
                        <label for="name" class="text-white">Amount</label>
                        <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Pay</span></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
        
        <div class="col-lg-9 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    
                    <div class="table-responsive">
                      <table id="table" data-search="true" 
                            data-pagination="true" 
                            data-locale="en-US"
                            data-toggle="table"
                            data-sortable="true" 
                            data-url="{{ route('upi_transfer_test_retailer') }}">
                        <thead>
                          <tr>
                            <th data-field="name">Name</th>
                            <th data-field="upi">UPI</th>
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
        $(document).ready(function() {
            // Initialize the Bootstrap Table
            $('#table').bootstrapTable();

            // Attach a click event handler to table rows
            $('#table tbody').on('click', 'tr', function() {
                // Get the row data
                var rowData = $(this).data('index');
                var row = $('#table').bootstrapTable('getData')[rowData];
                
                // Log the clicked row data
                console.log('Row data:', row);
                
                // Get specific cell data (for example, the UPI ID)
                var upi = row.upi;
                var name = row.name;
                console.log('UPI ID:', name);
                $('#name').val(name);
                $('#upi').val(upi);
                
            });
        });
function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}

function showPosition(position) {
  
  $("#latitude").val(position.coords.latitude);
  $("#longitude").val(position.coords.longitude);
}

getLocation();
</script>
@endsection
