@extends('new_layouts/app')

@section('title', 'DMT Login')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">DMT Login</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('bill_post_dmt_create_customer_artl_retailer') }}" method="post">
                        @csrf
                        <input type="hidden" value="{{ $bank_channel }}" id="bank_channel" name="bank_channel">
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number" value="{{ $customer_mobile }}" required="" readonly>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="" required="">
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Pincode</label>
                        <input type="text" class="form-control" name="pincode" id="pincode" placeholder="Pincode" value="" required="">
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
