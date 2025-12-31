@extends('new_layouts/app')

@section('title', 'BBPS')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
<style>
    .operator-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
</style>
<style>
        .custom-swal-popup {
            position: relative;
        }

        .custom-swal-image {
            position: absolute !important;
            top: 10px !important;
            right: 10px !important;
            margin: 0 !important;
            z-index: 1000;
        }

        .custom-swal-popup .swal2-header {
            padding-top: 120px; /* Adjust based on image height + spacing */
        }

        /* Keep title centered */
        .custom-swal-popup .swal2-title {
            text-align: center;
            margin: 0 auto;
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card bg-bbps-inner-page">
                
                <div class="card-body">
                    <h1 class="card-title text-black">{{ $operators->name }}</h1>
                    <img width="170" height="75" src="{{ asset('bbps/bbps_logo.png') }}" alt="bbps" style="position: absolute;
    right: 0;
    top: 0;"/></div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
                <div class="card bg-bbps-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-black">Payer Detail</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="" id="recharge_form" method="post">
                        @csrf
                       
                      <div class="form-group">
                       <label class="text-black">Mobile Number</label>
                       <input type="text" class="form-control" name="payer_mob" id="payer_mob" placeholder="Mobile Number" required="">
                      </div>
                      <div class="form-group">
                       <label class="text-black">Name</label>
                       <input type="text" class="form-control" name="payer_name" id="payer_name" placeholder="Name" required="">
                      </div>
                      <div class="button-container">
                            <button type="button" class="button btn btn-success" onclick="getBiller({{ $operators->id }})"><span>Next</span></button>
                      </div>
                      
                    </form>
                  </div>
                </div>
              </div>
    <div class="col-md-3 grid-margin stretch-card" id="biller_div">
        
    </div>
    
    <div class="col-md-3 grid-margin stretch-card" id="biller_param">
        
    </div>
    
    <div class="col-md-3 grid-margin stretch-card" id="biller_detail">
        
    </div>
    <button id="playButton" style="display: none;">Click to Play Audio</button>
    <audio id="backgroundAudio" preload="auto">
        <source src="{{ asset('BharatConnectMOGO270824.wav') }}" type="audio/wav">
        Your browser does not support the audio element.
    </audio>    
        
        
        <!--<div class="col-lg-12 grid-margin stretch-card">-->
        <!--        <div class="card bg-inner-page">-->
        <!--          <div class="card-body">-->
                    
        <!--            <div class="table-responsive">-->
        <!--              <table id="table" data-search="true" -->
        <!--                    data-pagination="true" -->
        <!--                    data-locale="en-US"-->
        <!--                    data-toggle="table"-->
        <!--                    data-sortable="true" -->
        <!--                    data-url="{{ route('recharge_report_data_last_retailer') }}">-->
        <!--                <thead>-->
        <!--                  <tr>-->
        <!--                    <th data-field="name">Network</th>-->
        <!--                    <th data-field="mobile">Mobile</th>-->
        <!--                    <th data-field="amount">Amount</th>-->
        <!--                    <th data-field="status" data-formatter="statuColunm">Status</th>-->
        <!--                  </tr>-->
        <!--                </thead>-->
                        
        <!--              </table>-->
        <!--            </div>-->
        <!--          </div>-->
        <!--        </div>-->
        <!--      </div>-->
    </div>
</div>
@endsection

@section('page-script')

<script>
    
        
        
        
    
    function getBiller(cat_id) {
        if (!cat_id) return;
        
        var pay_payer_mob = $("#payer_mob").val();
        var pay_payer_name = $("#payer_name").val();
        
        if(pay_payer_mob.length != 10 && pay_payer_name.length <= 2){
            alert('Please Enter Payer Detail');
            return false;
        }
        
        loaderShow();
        
        $.ajax({
            type: 'post',
            dataType: 'html',
            url: "{{ route('post_biller_bbps_retailer') }}",
            data: {
                "cat_id": cat_id,
                "_token": "{{ csrf_token() }}"
            },
            success: function (result) {
                loaderHide();
                
                $('.js-example-basic-single').select2('destroy');
                // Update the biller div content
                $('#biller_div').html(result);
                
                $(".js-example-basic-single").select2();
            },
            error: function(xhr, status, error) {
                loaderHide();
                console.log('AJAX Error:', error);
                alert('Error loading billers. Please try again.');
            }
        });
    }
    
    function getParam(biller_id) {
        if (!biller_id) return;
        
        loaderShow();
        
        $.ajax({
            type: 'post',
            dataType: 'html',
            url: "{{ route('post_biller_param_bbps_retailer') }}",
            data: {
                "biller_id": biller_id,
                "_token": "{{ csrf_token() }}"
            },
            success: function (result) {
                loaderHide();
                
                
                $('.js-example-basic-single').select2('destroy');
                // Update the biller div content
                $('#biller_param').html(result);
                
                $(".js-example-basic-single").select2();
                
                var pay_payer_mob = $("#payer_mob").val();
                var pay_payer_name = $("#payer_name").val();
                $("#pay_payer_mob").val(pay_payer_mob);
                $("#pay_payer_name").val(pay_payer_name);
            },
            error: function(xhr, status, error) {
                loaderHide();
                console.log('AJAX Error:', error);
                alert('Error loading billers. Please try again.');
            }
        });
    }
    function payBill(){
        window.location.href = "https://user.payritepayment.in/retailer-services/bbps/category/water?success=success";
    }
    
    function getFetch() {
        
        var isValid = true;
        var errorMessage = '';
        $('#biller_param_form input[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                
                errorMessage += $(this).attr('placeholder') + ' is required\n';
                
            } else {
                
            }
        });
        if (!isValid) {
            alert(errorMessage);
            return false;
        }
    
        $.ajax({
            url: "{{ route('post_bill_detail_bbps_retailer') }}",
            type: 'POST',
            dataType: 'html',
            data: $('#biller_param_form').serialize(),
            success: function(result) {
                loaderHide();
                
                
                
                // Update the biller div content
                $('#biller_detail').html(result);
                
                
                
                
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.log('Error: ' + error);
            }
        });
    }
    
    @if(Session::has('bbps_success'))
    function playAudioJQuery() {
            const $audio = $('#backgroundAudio');
            const $playButton = $('#playButton');
            
            $audio[0].play().then(() => {
                console.log('Audio started playing with jQuery');
            }).catch(error => {
                console.log('Autoplay prevented:', error);
                $playButton.show().on('click', function() {
                    $audio[0].play();
                    $playButton.hide();
                });
            });
        }

        // For user interaction-based triggers (more reliable)
        document.addEventListener('mousemove', function() {
            // This will work more reliably after user interaction
            const audio = document.getElementById('backgroundAudio');
            if (audio.paused) {
                audio.play().catch(console.error);
            }
        }, { once: true }); // Only trigger once
        
    Swal.fire({
          title: '₹100',
          text: 'Transaction Processed! Transaction ID ',
          icon: 'success',
          imageUrl: '{{ asset("bbps/assured.png") }}',
          imageWidth: 100,  // Set width in pixels
          imageHeight: 100, // Set height in pixels
          showCancelButton: true,
          confirmButtonText: "OK",
          customClass: {
                    popup: 'custom-swal-popup',
                    image: 'custom-swal-image'
                },
          cancelButtonText: "Receipt"
            }).then((result) => {
              if (result.isDismissed) {
                // If the user clicks the "Redirect" button
                window.open("https://user.payritepayment.in/receipt/bbps", "_blank");
                 // Replace with the URL you want to redirect to
            }
        });
    @endif
</script>
@endsection
