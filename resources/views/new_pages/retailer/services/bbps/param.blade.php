        
            <div class="card bg-bbps-inner-page">
                <div class="card-body">
                    <h4 class="card-title text-black">Pay Bill</h4>
                    <form class="forms-sample" action="" id="biller_param_form" method="post">
                        @csrf
                        <input type="hidden" name="pay_biller_id" id="pay_biller_id" value="{{ $biller->biller_id }}">
                        <input type="hidden" name="pay_payer_mob" id="pay_payer_mob" >
                        <input type="hidden" name="pay_payer_name" id="pay_payer_name" >
                        @foreach($param as $r)
                        <div class="form-group">
                            <label for="name" class="text-black">{{ $r->param_name }}</label>
                            <input type="text" class="form-control" name="param[]" id="param_{{ $r->id }}" placeholder="{{ $r->param_name }}" 
                                    required=""
                                    pattern="{{ $r->regex_pattern }}" 
                                    >
                        </div>
                        @endforeach
                        
                        <div class="form-group">
                            <label class="text-black">Amount</label>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" required="" minlength="{{ $r->min_length }}" 
                                    maxlength="{{ $r->max_length }}">-->
                        </div>
                        
                        <div class="button-container">
                            <button type="button" onclick="payBill()" class="button btn btn-primary"><span>Pay</span></button>
                            <button type="button" onclick="getFetch()" class="button btn btn-primary"><span>Fetch</span></button>
                            <button type="button" onclick="" class="button btn btn-primary"><span>Plan</span></button>
                        </div>
                        <div class="button-container">
                            
                        </div>
                    </form>
                </div>
            </div>
        