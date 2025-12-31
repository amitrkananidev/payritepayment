@extends('new_layouts/app')

@section('title', 'Change Password')

@section('page-style')
    
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="offset-md-3 col-md-6 col-sm-12 grid-margin stretch-card">
                <div class="card bg-red-section">
                  <div class="card-body">
                    <h4 class="card-title text-white">Change Password</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_change_password') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Current Password</label>
                        <input type="password" class="form-control" name="current_password" id="cpassword" placeholder="Current Password" required="">
                      </div>
                      <div class="form-group">
                        <label for="name" class="text-white">New Password</label>
                        <input type="password" class="form-control" name="new_password" id="newpassword" placeholder="New Password" required="">
                      </div>
                      <div class="form-group">
                        <label for="name" class="text-white">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" id="cnpassword" placeholder="Confirm Password" required="">
                      </div>
                      
                      <div class="button-container">
                        <button type="submit" class="button btn btn-primary"><span>Change</span></button>
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