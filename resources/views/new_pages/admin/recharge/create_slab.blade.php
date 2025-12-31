@extends('new_layouts/app')

@section('title', 'Create Slab')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Create Slab</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_recharge_create_slab') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="" oninput="convertToUppercase(this)">
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
