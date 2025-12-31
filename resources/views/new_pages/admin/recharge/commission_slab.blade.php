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
                    <form class="forms-sample" action="{{ route('post_recharge_slab_commission') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      <div class="form-group">
                       <label>Slab</label>
                       <select class="js-example-basic-single w-100" id="slab_id" name="slab_id" required="">
                           @foreach($slabs as $r)
                           <option value="{{ $r->id }}">{{ $r->name }}</option>
                           @endforeach
                       </select>
                      </div>
                      <div class="form-group">
                       <label>Network</label>
                       <select class="js-example-basic-single w-100" id="op_id" name="op_id" required="">
                           @foreach($operators as $ro)
                           <option value="{{ $ro->id }}">{{ $ro->name }}</option>
                           @endforeach
                       </select>
                      </div>
                      <div class="form-group">
                       <label>Type</label>
                       <select class="js-example-basic-single w-100" id="commission_type" name="commission_type" required="">
                           <option value="1">In percentage</option>
                           <option value="2">In flat</option>
                       </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="name">Commission</label>
                        <input type="text" class="form-control" name="commission" id="commission" placeholder="Commission" required="">
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
