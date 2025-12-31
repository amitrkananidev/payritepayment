@extends('new_layouts/app')

@section('title', 'Create Retailer')

@section('page-style')
<style>
#map {
  width: 100%;
  height: 500px;
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Create Retailer</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('post_create_retailer_distributor') }}" method="post" enctype="multipart/form-data">
                        @csrf
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" value="{{ old('surname') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile" maxlength="10" value="{{ old('mobile') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="dob">DOB</label>
                        <input type="date" class="form-control" name="dob" id="dob" placeholder="DOB" value="{{ old('dob') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="Address" value="{{ old('address') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="pincode">Pincode</label>
                        <input type="text" class="form-control" name="pincode" id="pincode" placeholder="Pincode" maxlength="6" value="{{ old('pincode') }}" required="">
                      </div>
                      
                      <div class="form-group">
                       <label>State</label>
                       <select class="js-example-basic-single w-100" id="states" name="states" onchange="getCity()" required="">
                           <option value="">Select State</option>
                           @foreach($states as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                           @endforeach
                       </select>
                      </div>
                      
                      <div class="form-group">
                       <label>City</label>
                       <select class="js-example-basic-single-city w-100" id="city" name="city" required="">
                           
                       </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="pan_number">Pan Number</label>
                        <input type="text" class="form-control" name="pan_number" id="pan_number" oninput="convertToUppercase(this)" maxlength="10" placeholder="Pan Number" value="{{ old('pan_number') }}" required="">
                      </div>
                      
                      <div class="form-group">
                        <label>Pan</label>
                        <input type="file" name="pan_image" class="file-upload-default" accept="image/*">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Pan">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="address">Aadhar Number</label>
                        <input type="text" class="form-control" name="aadhar_number" id="aadhar_number" maxlength="12" placeholder="Aadhar Number" value="{{ old('aadhar_number') }}" required="">
                      </div>
                      
                      <div class="form-group">
                        <label>Aadhar Front</label>
                        <input type="file" name="aadhaar_front_image" class="file-upload-default" accept="image/*">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Aadhar Front">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label>Aadhar Back</label>
                        <input type="file" name="aadhaar_back_image" class="file-upload-default" accept="image/*">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Aadhar Back">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="address">Shop Name</label>
                        <input type="text" class="form-control" name="shop_name" id="shop_name" placeholder="Shop Name" value="{{ old('shop_name') }}" required="">
                      </div>
                      <div class="form-group">
                        <label for="pincode">Shop Address</label>
                        <input type="text" class="form-control" name="shop_address" id="shop_address" placeholder="Shop Address" value="{{ old('shop_address') }}" required="">
                      </div>
                      <div class="form-group">
                        <label>Shop Image</label>
                        <input type="file" name="shop_image" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Shop Image" accept="image/*">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      <div class="form-group">
                        <label>Selfie</label>
                        <input type="file" name="selfie" class="file-upload-default">
                        <div class="input-group col-xs-12">
                          <input type="text" class="form-control file-upload-info" disabled placeholder="Selfie" accept="image/*">
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                      <div class="form-group row">
                          <div class="col-md-6 col-sm-6">
                          <label>latitude</label>
                          <input type="text" class="form-control" name="latitude" id="latitude" placeholder="latitude" value="{{ old('latitude') }}" required="">
                          </div>
                          <div class="col-md-6 col-sm-6">
                          <label>longitude</label>
                          <input type="text" class="form-control" name="longitude" id="longitude" placeholder="longitude" value="{{ old('longitude') }}" required="">
                          </div>
                          <div class="col-md-12 col-sm-12">
                          <label>Search Location</label>
                          <input type="text" class="form-control" id="search-box" placeholder="Search Location">
                          </div>
                          <div class="col-md-12 col-sm-12">
                          <div id="map"></div>
                          </div>
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
<script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
        ({key: "AIzaSyBNdPZHybJOOp0q3FUOg3Hp7U6t6nbiGIA", v: "weekly"});</script>
<script>
function convertToUppercase(inputElement) {
    // Convert the input value to uppercase
    inputElement.value = inputElement.value.toUpperCase();
}
function getCity() {
    var state = $("#states").val();
    $.ajax({
        type: 'get',
        dataType:'html',
        url: "{{ route('get_state_city') }}",
        data: {"state" : state ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $('#city').html(result);
            $(".js-example-basic-single-city").select2();
        }
    });
} 

async function initMap() {
        // Request needed libraries.
        const { Map } = await google.maps.importLibrary("maps");
        const { Autocomplete } = await google.maps.importLibrary("places");
        
        const myLatlng = { lat: 22.3039, lng: 70.8022 };
        const map = new google.maps.Map(document.getElementById("map"), {
          zoom: 10,
          center: myLatlng,
        });

        // Create the initial InfoWindow.
        let infoWindow = new google.maps.InfoWindow({
          content: "Click the map to get Lat/Lng!",
          position: myLatlng,
        });

        infoWindow.open(map);

        // Configure the click listener.
        map.addListener("click", (mapsMouseEvent) => {
          // Close the current InfoWindow.
          infoWindow.close();

          // Create a new InfoWindow.
          infoWindow = new google.maps.InfoWindow({
            position: mapsMouseEvent.latLng,
          });

          const latLng = mapsMouseEvent.latLng.toJSON();
          console.log(latLng.lat);
          console.log(latLng.lng);
          $("#latitude").val(latLng.lat);
          $("#longitude").val(latLng.lng);

          infoWindow.setContent(JSON.stringify(latLng, null, 2));
          infoWindow.open(map);
        });

        // Create the search box and link it to the UI element.
        const input = document.getElementById("search-box");
        const searchBox = new google.maps.places.Autocomplete(input);
        searchBox.bindTo("bounds", map);

        searchBox.addListener("place_changed", () => {
          const place = searchBox.getPlace();

          if (!place.geometry || !place.geometry.location) {
            window.alert("No details available for input: '" + place.name + "'");
            return;
          }

          // If the place has a geometry, then present it on a map.
          if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
          } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);  // Why 17? Because it looks good.
          }

          // Update the position and content of the InfoWindow.
          infoWindow.setPosition(place.geometry.location);
          const latLng = place.geometry.location.toJSON();
          console.log(latLng.lat);
          console.log(latLng.lng);
          $("#latitude").val(latLng.lat);
          $("#longitude").val(latLng.lng);

          infoWindow.setContent(JSON.stringify(latLng, null, 2));
          infoWindow.open(map);
        });
      }

initMap();
</script>

@endsection
