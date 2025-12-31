@extends('new_layouts/app')

@section('title', 'AEPS Merchant Onboarding')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">AEPS Merchant Onboarding</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('credo_aeps_registration_retailer') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      
                      <div class="form-group">
                        <label for="name" class="text-white">TITLE</label>
                        <select class="js-example-basic-single w-100 form-control" id="title" name="title" required="">
                               <option value="Mr">Mr</option>
                               <option value="Mrs">Mrs</option>
                               <option value="Miss">Miss</option>
                           </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">NAME</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ Auth::user()->name }} {{ Auth::user()->surname }}" readonly="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">ESTABLISH DATE Of SHOP</label>
                        <input type="date" class="form-control" name="establishedYear" id="establishedYear" placeholder="Established Date" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">ACCOUNT NUMBER</label>
                        <input type="text" class="form-control" name="bankAccountNumber" id="bankAccountNumber" placeholder="Account Number" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">IFSC</label>
                        <input type="text" class="form-control" name="ifsc" id="ifsc" placeholder="IFSC" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">ACCOUNT TYPE</label>
                        <select class="js-example-basic-single w-100 form-control" id="accountType" name="accountType" required="">
                               <option value="savings">Saving</option>
                               <option value="current">Current</option>
                           </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">CHEQUE NO</label>
                        <input type="text" class="form-control" name="CANCELLED_CHEQUE_NO" id="CANCELLED_CHEQUE_NO" placeholder="CHEQUE NO" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">CANCELLED CHEQUE</label>
                        <input type="file" name="CANCELLED_CHEQUE" class="file-upload-default" accept="image/*">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="CANCELLED CHEQUE">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">DEVICE MODEL</label>
                        <select class="js-example-basic-single w-100 form-control" id="device_model" name="device_model" required="">
                               <option value="Mantra">Mantra</option>
                               <option value="SecuGen">SecuGen</option>
                               <option value="3M">3M</option>
                               <option value="Morpho">Morpho</option>
                               <option value="Startek">Startek</option>
                               <option value="TMF20">TMF20</option>
                           </select>
                      </div>
                      
                      
                      <div class="form-group">
                        <label for="name" class="text-white">DEVICE SR.NO</label>
                        <input type="text" class="form-control" name="deviceSerialNumber" id="deviceSerialNumber" placeholder="DEVICE SR.NO" value="" required="">
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
        $(document).ready(function() {
            $('#pincode').on('input', function() {
                var pincode = $(this).val();
                if (pincode.length === 6) {
                    fetchLocationInfo(pincode);
                }
            });
            
            function fetchLocationInfo(pincode) {
                
                $.ajax({
                    type: 'get',
                    dataType:'json',
                    url: "{{ route('get_city_based_on_pin_retailer') }}",
                    data: {"pincode" : pincode ,"_token":"{{ csrf_token() }}"},
                    success: function (result) {
                        var city = result.city;
                        if(city.length >= 1){
                            $('#city').val(result.city);
                        }else{
                            $('#city').prop("readonly", false);
                        }
                        
                        $('#state').val(result.state);
                        
                    }
                });
            }    
            
        });
    </script>
@endsection
