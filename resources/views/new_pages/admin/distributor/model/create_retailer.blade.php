<!-- Modal -->
<div class="modal fade" id="CreateRetailerModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="CreateRetailerModelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">Create Retailer</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                          <div class="card-body">
                            <!--<p class="card-description"> Bordered layout </p>-->
                            <form class="forms-sample" action="{{ route('create_dist_to_retailer') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="" name="current_mobile" id="current_mobile">
                                
                              <div class="form-group">
                                <label for="name">New Mobile</label>
                                <input type="text" class="form-control" name="new_mobile" id="new_mobile" placeholder="Mobile" value="{{ old('new_mobile') }}" required="">
                              </div>
                              <div class="form-group">
                                <label for="name">New Email</label>
                                <input type="email" class="form-control" name="new_email" id="new_email" placeholder="Email" value="{{ old('new_email') }}" required="">
                              </div>
                              
                              <div class="button-container">
                                <button type="submit" class="button btn btn-primary"><span>Create</span></button>
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