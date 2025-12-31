@extends('new_layouts/app')

@section('title', 'BBPS Category')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card bg-bbps-inner-page">
                  <img width="170" height="75" src="{{ asset('bbps/bbps_logo.png') }}" alt="bbps" style="position: absolute;
    right: 0;
    top: 0;"/>
                    
                    
                    
                    <?php $ct = 0; ?>    
                    @foreach($operators as $r)
                    
                    @if($ct != $r->cat_type)
                        
                        <?php $ct = $r->cat_type; ?>
                        
                        @if($ct != 1)
                        </div>
                        @endif
                    <div class="card-body">
                    <h4 class="card-title text-black">{{ $categories[$ct] }}</h4>
                    @endif
                    
                    <a class="btn btn-primary mt-4" href="https://user.payritepayment.in/retailer-services/bbps/category/{{ $r->slug }}" style="background: #edf7ef !important;border: none;color: black;" id="" value="" onclick="">
                        <!--<dotlottie-player src="https://lottie.host/92f5f83d-80e5-4701-bdc1-9cf8f16f5919/jGL2G2Pgxk.json" background="transparent" speed="1" style="width: 150px; height: 150px;background: #edf7ef !important;" loop autoplay></dotlottie-player>-->
                        <img width="100" height="100" src="{{ asset('uploads/bbps/'.$r->Image) }}" alt="{{ $r->name }}"/>
                        <!--https://www.shopnservice.com/assets/img/bb/BBPS-logo-blk03.png-->
                        <br>{{ $r->name }}
                    </a>
                    
                    @endforeach
                    
                    </div>
                    
                    
                    
                    
                
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<!--<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    
@endsection
