@extends('new_layouts/app')

@section('title', 'Sender Mobile')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    
    <div class="row">
        <div class="col-md-4 grid-margin">
            <a class="button btn btn-primary" href="{{ route('get_create_sender_retailer') }}">Create Sender / Add Credit Card</a>
        </div>
    </div>
    <div class="row">
        
        @foreach($check as $r)
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-inner-page">
                <div class="card-body">
                    <h4 class="card-title text-white">{{ $r->name_in_pan }}</h4>
                    <h4 class="card-title text-white">{{ $r->pan }}</h4>
                    <h4 class="card-title text-white">{{ $r->aadhar_number }}</h4>
                    <h4 class="card-title text-white">{{ $r->mobile }}</h4>
                    <h4 class="card-title text-white">XXXX XXXX XXXX {{ substr($r->card_number, -4) }}</h4>
                    
                    <a class="button btn btn-primary" href="{{ route('get_benf_retailer', $r->sender_id) }}">Next</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('page-script')

<script>
const x = document.getElementById("demo");

function getLocation() {
    
  if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else { 
      
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}

function showPosition(position) {
  
  $("#latitude").val(position.coords.latitude);
  $("#longitude").val(position.coords.longitude);
}

getLocation();
</script>

@endsection
