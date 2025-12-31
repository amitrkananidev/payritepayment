@extends('new_layouts/app')

@section('title', 'CCPAYOUT - Sender')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">Create Sender</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_create_sender_retailer') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      
                      
                      <div class="form-group row">
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Name As Per PAN</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Name As Per PAN" value="{{ old('name') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">PAN</label>
                            <input type="text" class="form-control" name="pan" id="pan" placeholder="PAN" value="{{ old('pan') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">AADHAR</label>
                            <input type="text" class="form-control" name="aadhar" id="aadhar" placeholder="AADHAR" value="{{ old('aadhar') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number" value="{{ old('mobile') }}" required="">
                          </div>
                        </div>  
                        <div class="form-group row">
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Credit Card Number</label>
                            <input type="text" class="form-control" name="card_number" id="card_number" placeholder="Credit Card Number" value="{{ old('card_number') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Credit Card CVV</label>
                            <input type="text" class="form-control" name="cvv" id="cvv" placeholder="Credit Card CVV" value="{{ old('cvv') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Credit Card Expiry</label>
                            <input type="text" class="form-control" name="expiry" id="expiry" placeholder="Credit Card Expiry" value="{{ old('expiry') }}" required="">
                          </div>
                          
                          <div class="col-sm-4">
                            <label for="name" class="text-white">Card Type</label>
                            <select class="js-example-basic-single w-100 form-control" id="card_type" name="card_type" required="">
                               <option value="">Select</option>
                               <option value="visa">visa</option>
                               <option value="rupay">Rupay</option>
                               <option value="master">Master</option>
                           </select>
                          </div>
                          
                          <div class="col-sm-4">
                                    <label for="name" class="text-white">Credit Card Front</label>
                                    <input type="file" name="cc_front" class="file-upload-default" accept="image/*">
                                    <div class="input-group col-xs-12">
                                      <input type="text" class="form-control file-upload-info" disabled placeholder="Credit Card Front">
                                      <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="height: 100%;color:white">Upload</button>
                                      </span>
                                    </div>
                            </div>
                            
                            <div class="col-sm-4">
                                    <label for="name" class="text-white">Credit Card Back</label>
                                    <input type="file" name="cc_back" class="file-upload-default" accept="image/*">
                                    <div class="input-group col-xs-12">
                                      <input type="text" class="form-control file-upload-info" disabled placeholder="Credit Card Back">
                                      <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="height: 100%;color:white">Upload</button>
                                      </span>
                                    </div>
                            </div>
                      </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Next</span></button>
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
const x = document.getElementById("demo");

function getLocation() {
    
  if(navigator.geolocation) {
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
