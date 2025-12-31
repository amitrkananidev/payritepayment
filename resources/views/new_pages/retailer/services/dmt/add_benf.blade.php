<!-- Modal -->
<div class="modal fade" id="add_benf" tabindex="-1" aria-labelledby="add_benfLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Beneficiary</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="kycdocumentbody">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Add Beneficiary</h4>
                        <!--<p class="card-description"> Bordered layout </p>-->
                        <form class="forms-sample" action="{{ route('post_dmt_benf_add_retailer') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" value="{{ $customer_mobile }}" id="customer_mobile" name="mobile" />
                            <input type="hidden" value="0" id="is_verify" name="is_verify" />
                          <div class="form-group row">
                              <div class="col-md-9 col-sm-12">
                                
                                <input type="text" class="form-control" name="benf_name" id="benf_name" placeholder="Account Holder Name" value="{{ old('benf_name') }}" required="">
                              </div>
                              <div class="col-md-3 col-sm-12">
                                  <button class="btn btn-primary" type="button" onclick="verifyBenf()" style="height: 44px;">
                                    Verify
                                  </button>
                              </div>
                          </div>
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="number" id="number" placeholder="Account Number" value="{{ old('number') }}" required="">
                          </div>
                          
                          <div class="form-group add_benf_bank_drop">
                           
                           <select class="js-example-basic-single-bank w-100" id="banks" name="banks" required="">
                               <option value="">Select Bank</option>
                               @foreach($banks as $r)
                                <option value="{{ $r->id }}" data-ifsc="{{ $r->ifsc }}">{{ $r->name }}</option>
                               @endforeach
                           </select>
                          </div>
                          
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="ifsc" id="ifsc" placeholder="IFSC" value="{{ old('ifsc') }}" required="">
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
      <div class="modal-footer">
        
      </div>
    </div>
  </div>
</div>