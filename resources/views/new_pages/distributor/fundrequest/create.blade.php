@extends('new_layouts/app')

@section('title', 'Create Request')

@section('page-style')
    <style>
        /* Basic styles for visibility */
        .hidden {
            display: none;
        }
        .select2-container--default{
            width:100% !important;
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card bg-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-white">Create Request</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_create_fund_request_distributor') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-white">Payment Mode</label>
                        <div class="col-sm-4">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="payment_mode" id="Cash" value="Cash" checked=""> CASH <i class="input-helper"></i></label>
                             </div>
                        </div>
                        <div class="col-sm-5">
                             <div class="form-check">
                            <label class="form-check-label text-white">
                                 <input type="radio" class="form-check-input" name="payment_mode" id="Bank" value="Bank"> BANK <i class="input-helper"></i></label>
                             </div>
                        </div>
                      </div>
                      <div class="form-group hidden" id="banks">
                       <label class="text-white">Select Bank</label>
                       <select class="js-example-basic-single" id="bank" name="bank_id">
                           <option value="">Select Bank</option>
                           @foreach($fund_banks as $r)
                            <option value="{{ $r->id }}">{{ $r->holder_name }} / {{ $r->account_number }} / {{ $r->ifsc }}</option>
                           @endforeach
                       </select>
                      </div>
                      <div class="form-group hidden" id="banks_ref">
                        <label for="name" class="text-white">Bank Ref</label>
                        <input type="text" class="form-control" name="bank_ref" id="bank_ref" placeholder="Bank Ref" value="{{ old('bank_ref') }}" >
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Amount</label>
                        <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="{{ old('amount') }}" required="">
                      </div>
                      
                      <div class="form-group row">
                        <div class="col">
                        <label for="name" class="text-white">Date</label>
                        <div class='input-group date' id='datetimepicker1'>
                            <input type="date" class="form-control" name="deposit_date" id="date" placeholder="Date" value="{{ old('date') }}" required="">
                        </div>
                        </div>
                        
                        <div class="col">
                        <label for="name" class="text-white">Time</label>
                        <div class='input-group date' id='datetimepicker1'>
                            <input type="time" class="form-control" name="deposit_time" id="time" placeholder="Time" value="{{ old('time') }}" required="">
                        </div>
                        </div>
                      </div>
                      
                      <div class="form-group" id="img">
                        <label class="text-white">Attechment</label>
                        <input type="file" name="img" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Attechment">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="name" class="text-white">Remark</label>
                        <input type="text" class="form-control" name="remark" id="remark" placeholder="Remark" value="{{ old('remark') }}" required="">
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
        $(document).ready(function() {
            // Function to show/hide sections based on the selected radio button
            function toggleSections() {

                // Get the selected transfer_type value
                var selectedValue = $('input[name="payment_mode"]:checked').val();

                // Show the corresponding section based on the selected value
                if (selectedValue === 'Bank') {
                    $('#banks').removeClass('hidden');
                    $('#banks_ref').removeClass('hidden');
                    $('#img').removeClass('hidden');
                } else{
                    $('#banks').addClass('hidden');
                    $('#banks_ref').addClass('hidden');
                    $('#img').addClass('hidden');
                }
                // Add more conditions for other values as needed
            }

            // Trigger the toggle function on page load to set the correct visibility
            toggleSections();

            // Attach the change event listener to radio buttons
            $('input[name="payment_mode"]').change(function() {
                toggleSections();
            });
        });
    </script>
@endsection
