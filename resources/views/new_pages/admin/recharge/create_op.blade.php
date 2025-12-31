@extends('new_layouts/app')

@section('title', 'Create Network')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Create Network</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_recharge_create_op') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      <div class="form-group">
                       <label>Type</label>
                       <select class="js-example-basic-single w-100" id="op_type" name="op_type" required="">
                           <option value="1">Mobile Prepaid</option>
                           <option value="2">DTH</option>
                       </select>
                      </div>
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="" oninput="convertToUppercase(this)">
                      </div>
                      <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" name="op_code" id="op_code" placeholder="OP Code" value="{{ old('op_code') }}" required="">
                      </div>
                      
                      <div class="form-group">
                        <label>Icon</label>
                        <input type="file" name="op_image" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Icon">
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
</script>
@endsection
