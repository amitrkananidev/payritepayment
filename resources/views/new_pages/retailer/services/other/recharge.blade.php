@extends('new_layouts/app')

@section('title', 'Recharge')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
<style>
    .operator-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">Recharge</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_recharge_retailer') }}" id="recharge_form" method="post">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <input type="hidden" name="recharge_type" id="recharge_type" value="PREPAID">
                      <div class="form-group">
                       <label class="text-white">Network</label>
                       <select class="js-example-basic-singlea w-100" id="op_code" name="op_code" required="">
                           <option value="" data-image="">Select</option>
                           @foreach($operators as $ro)
                           <option value="{{ $ro->op_code }}" data-image="https://user.payritepayment.in/uploads/recharge/{{ $ro->op_image }}">{{ $ro->name }}</option>
                           @endforeach
                       </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Mobile</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Amount</label>
                        <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="button" onclick="confirmBox()" class="button btn btn-primary"><span>Pay</span></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
        
        <div class="col-lg-9 grid-margin stretch-card">
            <div class="card bg-inner-page">
                <div class="card-body">
                    <div class="table-responsive">
                        <table
                          id="table-offer"
                          data-search="true"
                          data-locale="en-US"
                          data-toggle="table"
                          data-height="460"
                          data-url="https://user.payritepayment.in/api/v1/mplan/recharge"
                        >
                          <thead>
                            <tr>
                              <th data-field="rs" data-width="100">
                                Amount
                              </th>
                              <th data-field="desc" data-width="100">
                                Detail
                              </th>
                            </tr>
                          </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    
                    <div class="table-responsive">
                      <table id="table" data-search="true" 
                            data-pagination="true" 
                            data-locale="en-US"
                            data-toggle="table"
                            data-sortable="true" 
                            data-url="{{ route('recharge_report_data_last_retailer') }}">
                        <thead>
                          <tr>
                            <th data-field="name">Network</th>
                            <th data-field="mobile">Mobile</th>
                            <th data-field="amount">Amount</th>
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

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script>
    function confirmBox(){
        
        var beneficiaries = $('#op_code option:selected').text();
        var details = beneficiaries.split('|');
        var amount = $("#amount").val();
        var mobile = $("#mobile").val();
        var latitude = $("#latitude").val();
        var error = '<b class="text-success">GOOD TO GO!</b>';
        if (latitude === "") {
            var error = '<b class="text-danger">Please Check Your Location Permission!</b>';
        }
        var html = error+'<table class="table"><tbody><tr><td class="text-start">Network</td><td class="text-start">'+ beneficiaries +'</td></tr> <tr><td class="text-start">Mobile</td><td class="text-start">'+ mobile +'</td></tr> <tr class="thead-light text-start"><td><strong>Amount</strong></td><td class="text-start"><strong><u><i>₹ '+ amount +'</i></u></strong> (WALLET : ₹ {{ Auth::user()->wallet->balanceFloat }})</td></tr></tbody></table>';
        Swal.fire({
          title: "",
          icon: "info",
          html: html,
          showCloseButton: true,
          showCancelButton: true,
          focusConfirm: false,
          confirmButtonText: `
            <i class="fa fa-thumbs-up"></i> Great!
          `,
          confirmButtonAriaLabel: "Thumbs up, great!",
          cancelButtonText: `
            <i class="fa fa-thumbs-down"></i>
          `,
          cancelButtonAriaLabel: "Thumbs down"
        }).then((result) => {
          if (result.isConfirmed) {
            submitform();  // Call your function here
          }
        });
    }
    
    function submitform(){
        loaderShow();
        $("#recharge_form").submit();
    }
    
        $(document).ready(function() {
            // Initialize Select2
            $('.js-example-basic-singlea').select2({
                templateResult: formatOperator,
                templateSelection: formatOperator
            });
            
            // Custom formatter for operators with images
            function formatOperator(operator) {
                if (!operator.id) {
                    return operator.text;
                }
                
                var image = $(operator.element).data('image');
                var $operator = $(
                    '<div class="select-with-image">' +
                        '<img src="' + image + '" class="operator-img" />' +
                        '<span class="operator-name">' + operator.text + '</span>' +
                    '</div>'
                );
                
                return $operator;
            }
        });
        
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
        
    
    function statuColunm(value, row, index) {
      if(value == 1){
        return [
          '<label class="badge badge-success">Success</label>'
        ].join('')
      }else if(value == 2 || value == 3){
          return [
          '<label class="badge btn-inverse-danger">Failed</label>'
        ].join('')
      }else{
        return [
          '<label class="badge btn-inverse-warning">Pending</label>'
        ].join('')
      }
  }
        
function getLocationUsingGoogleAPI() {
  // Replace with your actual API key
  const apiKey = "AIzaSyBNdPZHybJOOp0q3FUOg3Hp7U6t6nbiGIA";
  
  // Make a POST request to the Google Geolocation API
  fetch("https://www.googleapis.com/geolocation/v1/geolocate?key=" + apiKey, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    // You can include WiFi access points or cell towers info for better accuracy
    body: JSON.stringify({})
  })
  .then(response => response.json())
  .then(data => {
    console.log("Google API location:", data);
    
    // Update your form fields
    $("#latitude").val(data.location.lat);
    $("#longitude").val(data.location.lng);
    $("#accuracy").val(data.accuracy);
  })
  .catch(error => {
    console.error("Error getting location from Google API:", error);
  });
}

getLocationUsingGoogleAPI();

        $(function() {
            var inputField = $("#mobile");
            
            // Function to validate input is numeric only
            function isNumeric(value) {
                return /^\d*$/.test(value);
            }
            
            // Use jQuery on() instead of addEventListener
            inputField.on('input', function() {
                var value = $(this).val();
                var op = $('#op_code').val();
                
                // Restrict to numbers only
                if (!isNumeric(value)) {
                    $(this).val(value.replace(/\D/g, ''));
                    value = $(this).val();
                }
                
                // Show alert and make AJAX call when 10 digits are entered
                if (value.length === 10) {
                    // Make AJAX call
                    $('#table-offer').bootstrapTable();
                    
                    $('#table-offer').bootstrapTable('refresh', {
                        url: 'https://user.payritepayment.in/api/v1/mplan/recharge?mobile='+value+'&op='+op,
                        silent: false
                    });
                }
            });
            
            $('#table-offer').on('click-row.bs.table', function(e, row, $element) {
                // Get the value from the first column of the clicked row
                const firstColumnValue = row.rs;
                
                // Show an alert with the value
                $('#amount').val(firstColumnValue);
              });
        });
</script>
@endsection
