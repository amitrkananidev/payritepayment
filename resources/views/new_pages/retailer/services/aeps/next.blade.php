@extends('new_layouts/app')

@section('title', 'Active AEPS Service')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Active AEPS Service</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('aeps_otp_retailer') }}" method="post">
                        @csrf
                      
                      <div class="form-group">
                        <label for="name">Device Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Device Name" value="{{ old('name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="name">Device Number</label>
                        <input type="text" class="form-control" name="number" id="number" placeholder="Device Number" value="{{ old('number') }}" required="">
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
