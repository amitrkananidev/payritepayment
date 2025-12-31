@extends('new_layouts/app')

@section('title', 'AEPS Service Registration')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">AEPS Service Registration</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('aeps_registration_retailer') }}" method="post">
                        @csrf
                      
                      <div class="form-group">
                       <label>State</label>
                       <select class="js-example-basic-single w-100" id="state" name="state" required="">
                           <option value="">Select State</option>
                           @foreach($states as $r)
                            <option value="{{ $r->stateId }}">{{ $r->state }}</option>
                           @endforeach
                       </select>
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

@endsection
