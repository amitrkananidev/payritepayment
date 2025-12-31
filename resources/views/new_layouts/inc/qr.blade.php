<!-- Modal -->
<div class="modal fade" id="qr_cust_detail" tabindex="-1" aria-labelledby="qr_cust_detailLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">UPI CUSTOMER DETAIL</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="kycdocumentbody">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                      <div class="card-body" id="qr_reg_section">
                        
                        <p class="card-description text-danger" id="qr-error-messages"> </p>
                        <form class="forms-sample" action="" method="post" enctype="multipart/form-data">
                            @csrf
                            
                          
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="cust_name" id="qr_cust_name" placeholder="Name" required="">
                          </div>
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="cust_surname" id="qr_cust_surname" placeholder="Surname" required="">
                          </div>
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="cust_mobile" id="qr_cust_mobile" placeholder="Mobile" required="">
                          </div>
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="amount" id="qr_amount" placeholder="Amount" required="">
                          </div>
                          
                          <div class="button-container">
                            <button type="button" class="button btn btn-primary" id="qr_send_otp_btn" onClick="qrPaymentOtpSend()"><span>Send OTP</span></button>
                          </div>
                        </form>
                      </div>
                      <div class="card-body hide" id="qr_otp_section">
                          <p class="card-description text-danger" id="qr-error-messages"> </p>
                        <form class="forms-sample" action="" method="post" enctype="multipart/form-data">
                            @csrf
                            
                          <input type="hidden" id="qr_transaction_id"/>
                          <div class="form-group">
                            
                            <input type="text" class="form-control" name="cust_otp" id="qr_cust_otp" placeholder="OTP" required="">
                          </div>
                          
                          <div class="button-container">
                            <button type="button" class="button btn btn-primary" onClick="qrPaymentOtpVerify()"><span>Verify</span></button>
                          </div>
                        </form>
                      </div>
                      <div class="card-body hide" id="qr_img_section">
                          <img id="qr_img" />
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