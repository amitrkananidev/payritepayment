<div class="card bg-bbps-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-black">Biller</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="" id="recharge_form" method="post">
                        @csrf
                        
                      <div class="form-group">
                       <label class="text-black">Biller</label>
                       <select class="js-example-basic-single w-100" id="biller_id" name="biller_id" required="" onchange="getParam(this.value)">
                           <option value="" data-image="">Select</option>
                           @foreach($operators as $ro)
                           <option value="{{ $ro->biller_id }}">{{ $ro->biller_name }}</option>
                           @endforeach
                       </select>
                      </div>
                      
                    </form>
                  </div>
                </div>
