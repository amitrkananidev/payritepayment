@extends('new_layouts/app')

@section('title', 'Create Retailer')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Create Retailer</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_create_retailer') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" value="{{ old('surname') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile" maxlength="10" value="{{ old('mobile') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="dob">DOB</label>
                        <input type="date" class="form-control" name="dob" id="dob" placeholder="DOB" value="{{ old('dob') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="Address" value="{{ old('address') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="pincode">Pincode</label>
                        <input type="text" class="form-control" name="pincode" id="pincode" placeholder="Pincode" maxlength="6" value="{{ old('pincode') }}" required="">
                      </div>
                      
                      <div class="form-group">
                       <label>State</label>
                       <select class="js-example-basic-single w-100" id="states" name="states" onchange="getCity()" required="">
                           <option value="">Select State</option>
                           @foreach($states as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                           @endforeach
                       </select>
                      </div>
                      
                      <div class="form-group">
                       <label>City</label>
                       <select class="js-example-basic-single-city w-100" id="city" name="city" required="">
                           
                       </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="pan_number">Pan Number</label>
                        <input type="text" class="form-control" name="pan_number" id="pan_number" oninput="convertToUppercase(this)" maxlength="10" placeholder="Pan Number" value="{{ old('pan_number') }}" required="">
                      </div>
                      
                      <div class="form-group">
                        <label>Pan</label>
                        <input type="file" name="pan_image" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Pan">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="address">Aadhar Number</label>
                        <input type="text" class="form-control" name="aadhar_number" id="aadhar_number" maxlength="12" placeholder="Aadhar Number" value="{{ old('aadhar_number') }}" required="">
                      </div>
                      
                      <div class="form-group">
                        <label>Aadhar Front</label>
                        <input type="file" name="aadhaar_front_image" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Aadhar Front">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label>Aadhar Back</label>
                        <input type="file" name="aadhaar_back_image" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Aadhar Back">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="address">Shop Name</label>
                        <input type="text" class="form-control" name="shop_name" id="shop_name" placeholder="Shop Name" value="{{ old('shop_name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="pincode">Shop Address</label>
                        <input type="text" class="form-control" name="shop_address" id="shop_address" placeholder="Shop Address" value="{{ old('shop_address') }}" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Submit</span></button>
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
function convertToUppercase(inputElement) {
    // Convert the input value to uppercase
    inputElement.value = inputElement.value.toUpperCase();
}
function getCity() {
    var state = $("#states").val();
    $.ajax({
        type: 'get',
        dataType:'html',
        url: "{{ route('get_state_city') }}",
        data: {"state" : state ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $('#city').html(result);
            $(".js-example-basic-single-city").select2();
        }
    });
}    
</script>
@endsection
