
<div class="card bg-bbps-inner-page">
                  <div class="card-body">
                    <h4 class="card-title text-black">Information</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="" id="recharge_form" method="post">
                        @csrf
                        
                      <div class="form-group">
                       <label class="text-black"><b>Customer Name :</b></label>
                       <label class="text-black">{{ $decode->billerResponse->accountHolderName }}</label>
                      </div>
                      <div class="form-group">
                       <label class="text-black"><b>Amount :</b></label>
                       <label class="text-black">{{ $decode->billerResponse->amount }}</label>
                      </div>
                      <div class="form-group">
                       <label class="text-black"><b>DueDate :</b></label>
                       <label class="text-black">{{ $decode->billerResponse->dueDate }}</label>
                      </div>
                      <div class="form-group">
                       <label class="text-black"><b>BillDate :</b></label>
                       <label class="text-black">{{ $decode->billerResponse->billDate }}</label>
                      </div>
                      <div class="form-group">
                       <label class="text-black"><b>BillPeriod :</b></label>
                       <label class="text-black">{{ $decode->billerResponse->billPeriod }}</label>
                      </div>
                      
                    </form>
                  </div>
                </div>
