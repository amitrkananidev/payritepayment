<!-- Modal -->
<div class="modal fade" id="odModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="odModelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">OD Transfer</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                          <div class="card-body">
                            <h4 class="card-title">OD Transfer</h4>
                            <!--<p class="card-description"> Bordered layout </p>-->
                            <form class="forms-sample" action="{{ route('fund_od') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="" name="mobile" id="retailer_mobile_od">
                                <div class="form-group">
                                  
                              <select class="js-example-basic-single w-100" id="type" name="type" required="">
                                   <option value="">OD Type</option>
                                   <option value="Credit">Credit</option>
                                   <option value="Debit">Debit</option>
                               </select>
                               </div>
                               <div class="form-group">
                                  
                              <select class="js-example-basic-single w-100" id="is_od" name="is_od" required="">
                                   <option value="">Is It OD?</option>
                                   <option value="1">Yes</option>
                                   <option value="0">No</option>
                               </select>
                               </div>
                              <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="{{ old('amount') }}" required="">
                              </div>
                              <div class="form-group">
                                <label for="name">Remark</label>
                                <input type="text" class="form-control" name="remark" id="remark" placeholder="Remark" value="{{ old('remark') }}" required="">
                              </div>
                              
                              <div class="button-container">
                                <button type="submit" class="button btn btn-primary"><span>Transfer</span></button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
            </div>
      </div>
      <div class="modal-footer">
        <!--<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
        <!--<button type="button" class="btn btn-primary">Understood</button>-->
      </div>
    </div>
  </div>
</div>