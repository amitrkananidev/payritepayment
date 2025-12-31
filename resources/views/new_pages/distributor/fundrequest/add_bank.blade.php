@extends('new_layouts/app')

@section('title', 'Add Bank')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Add Bank</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_add_bank_fund_distributor') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      <div class="form-group">
                        <label for="name">Account Holder Name</label>
                        <input type="text" class="form-control" name="holder" id="holder" placeholder="Account Holder Name" value="{{ old('holder') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="surname">Account Number</label>
                        <input type="text" class="form-control" name="account" id="account" placeholder="Account Number" value="{{ old('account') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="surname">IFSC</label>
                        <input type="text" class="form-control" name="ifsc" id="ifsc" placeholder="IFSC" value="{{ old('ifsc') }}" required="">
                      </div>
                      <p class="card-description">Accepted By</p>
                      <div class="form-check form-check-primary">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="transfer_type[]" value="NEFT"> NEFT </label>
                      </div>
                      <div class="form-check form-check-info">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="transfer_type[]" value="RTGS"> RTGS </label>
                      </div>
                      <div class="form-check form-check-warning">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="transfer_type[]" value="IMPS"> IMPS </label>
                      </div>
                      <div class="form-check form-check-success">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="transfer_type[]" value="UPI"> UPI </label>
                      </div>
                      <div class="form-check form-check-danger">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="transfer_type[]" value="BANK DEPOSIT"> BANK DEPOSIT </label>
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

@endsection
