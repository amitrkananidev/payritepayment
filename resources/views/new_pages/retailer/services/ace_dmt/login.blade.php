@extends('new_layouts/app')

@section('title', 'DMT 23 Login')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">DMT 3 Login</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('ace_post_dmt_login_retailer') }}" method="post">
                        @csrf
                      <input type="hidden" name="latitude" id="latitude" value="">
                      <input type="hidden" name="longitude" id="longitude" value="">
                      <div class="form-group">
                        <label for="name" class="text-white">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number" value="{{ old('mobile') }}" required="">
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-white">CHANNEL</label>
                        <div class="col-sm-3">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="bank_channel" id="DMT1" value="1" checked=""> FINO <i class="input-helper"></i></label>
                             </div>
                        </div>
                        <div class="col-sm-3">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="bank_channel" id="DMT2" value="2"> NSDL <i class="input-helper"></i></label>
                             </div>
                        </div>
                        
                        <div class="col-sm-3">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="bank_channel" id="DMT4" value="4"> AIRTEL <i class="input-helper"></i></label>
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
