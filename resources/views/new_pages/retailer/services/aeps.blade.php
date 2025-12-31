@extends('new_layouts/app')

@section('title', 'AEPS')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">AEPS</h4>
                         <iframe id="aepsGatewayIframe" name="aepsGatewayIframe" width="600" height="400"></iframe>

                  </div>
                </div>
              </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/aeps.js') }}"></script>
<?php
$currentTime = date( 'd-m-Y h:i:s A', time () );

?>

<script>
// document.addEventListener("DOMContentLoaded", function () {
//             // Initialize the AEPS Gateway
//             var aeps = new EkoAEPSGateway();

//             aeps.config({
//                 partner_name: "{{ $data->partner_name }}",
//                 initiator_logo_url: "{{ $data->logo }}",
//                 initiator_id: "{{ $data->initiator_id }}",
//                 developer_key: "{{ $data->developer_key }}",
//                 secret_key: "{{ $data->secret_key }}",
//                 secret_key_timestamp: "{{ $data->secret_key_timestamp }}",
//                 user_code: "{{ $data->user_code }}",
//                 language: "en",
//                 environment: "{{ $data->logo }}"
//             });

//             // Open the AEPS Gateway in the iframe
//             aeps.setResponseCallbackFunction(function(response) {
//                 console.log("Response from AEPS Gateway:", response);
//             });

//             aeps.setConfirmationCallbackFunction(function(confirmation) {
//                 console.log("Confirmation needed for transaction:", confirmation);
//                 aeps.confirmTransaction();
//             });

//             aeps.open();
//         });

var aeps = new EkoAEPSGateway();

// Configure your developer API details...
aeps.config({
    "partner_name": "{{ $data->partner_name }}",
    "initiator_logo_url": "{{ $data->logo }}",
    "initiator_id": "{{ $data->initiator_id }}",
    "developer_key": "{{ $data->developer_key }}",
    "secret_key": "{{ $data->secret_key }}",
    "secret_key_timestamp": "{{ $data->secret_key_timestamp }}",
    "user_code": "{{ $data->user_code }}",
    "language": "en",
    "environment": "{{ $data->environment }}" 
});

aeps.setCallbackURL('{{ $data->callbackurl }}');    

aeps.open();
</script>
@endsection


