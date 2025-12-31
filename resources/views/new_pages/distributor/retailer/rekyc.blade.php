@extends('new_layouts/app')

@section('title', 'ReKyc')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">ReKyc</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_rekyc_distributor') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      
                      <div class="form-group">
                       <label>Select Retailer</label>
                       <select class="js-example-basic-single w-100" id="mobile" name="mobile" onchange="getUserDetail()" required="">
                           <option value="">Select Retailer</option>
                           @foreach($data as $r)
                            <option value="{{ $r->mobile }}">{{ $r->user_name }} - {{ $r->mobile }}</option>
                           @endforeach
                       </select>
                      </div>
                      
                      <div class="form-group">
                       <label>Onboarding Proccess Start From</label>
                       <select class="js-example-basic-single w-100" id="onboarding_proccess" name="onboarding_proccess" required="">
                           <option value="1">AEPS Service Activation</option>
                           <option value="0">Start As Fresh Onboarding</option>
                       </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" value="{{ old('surname') }}" required="">
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

function getUserDetail() {
    var mobile = $("#mobile").val();
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('get_user_detail_distributor') }}",
        data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $("#name").val(result.name);
            $("#surname").val(result.surname);
            $("#pan_number").val(result.pan_number);
            $("#aadhar_number").val(result.aadhaar_number);
        }
    });
}      
</script>
@endsection
