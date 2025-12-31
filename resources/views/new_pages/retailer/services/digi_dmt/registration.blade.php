@extends('new_layouts/app')

@section('title', 'DIGIKHATA Login')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">DIGIKHATA Registration</h4>
                    
                    <form class="forms-sample" action="{{ route('digi_post_dmt_create_customer_retailer') }}" method="POST" id="scandata">
                       @csrf
                       <input type="hidden" value="{{ $dmt_token }}" id="dmt_token" name="dmt_token">
                      <div class="form-group">
                        <label for="name" class="text-white">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number" value="{{ $customer_mobile }}" required="" readonly>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Pincode</label>
                        <input type="text" class="form-control" name="pincode" id="pincode" placeholder="Pincode" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Aadhar</label>
                        <input type="text" class="form-control" name="aadharno" id="aadharno" placeholder="aadharno" value="" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="button" class="button btn btn-primary" onclick="submitForm()"><span>Next</span></button>
                      </div>
                      
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>
@endsection

@section('page-script')
    <script>
        
        function submitForm() {
            $("#scandata").submit();
        }
    </script>
@endsection
