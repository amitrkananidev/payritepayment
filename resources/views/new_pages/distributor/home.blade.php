@extends('new_layouts/app')

@section('title', 'Dashboard')

@section('page-style')

@endsection

@section('content')
        <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="home-tab">
                  <!--<div class="d-sm-flex align-items-center justify-content-between border-bottom">-->
                  <!--  <ul class="nav nav-tabs" role="tablist">-->
                  <!--    <li class="nav-item">-->
                  <!--      <a class="nav-link active ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>-->
                  <!--    </li>-->
                  <!--    <li class="nav-item">-->
                  <!--      <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab" aria-selected="false">Audiences</a>-->
                  <!--    </li>-->
                  <!--    <li class="nav-item">-->
                  <!--      <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab" aria-selected="false">Demographics</a>-->
                  <!--    </li>-->
                  <!--    <li class="nav-item">-->
                  <!--      <a class="nav-link border-0" id="more-tab" data-bs-toggle="tab" href="#more" role="tab" aria-selected="false">More</a>-->
                  <!--    </li>-->
                  <!--  </ul>-->
                  <!--  <div>-->
                  <!--    <div class="btn-wrapper">-->
                  <!--      <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Share</a>-->
                  <!--      <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>-->
                  <!--      <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Export</a>-->
                  <!--    </div>-->
                  <!--  </div>-->
                  <!--</div>-->
                  <div class="tab-content tab-content-basic">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                      <!--<div class="row">-->
                      <!--  <div class="col-sm-12">-->
                      <!--    <div class="statistics-details d-flex align-items-center justify-content-between">-->
                      <!--      <div>-->
                      <!--        <p class="statistics-title">Wallet Balance</p>-->
                      <!--        <h3 class="rate-percentage">{{ Auth::user()->wallet->balanceFloat }}</h3>-->
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>-0.5%</span></p>-->
                      <!--      </div>-->
                      <!--      <div>-->
                      <!--        <p class="statistics-title">Total Retailer</p>-->
                      <!--        <h3 class="rate-percentage">{{ $total_retailer }}</h3>-->
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+0.1%</span></p>-->
                      <!--      </div>-->
                      <!--      <div>-->
                      <!--        <p class="statistics-title">Pending Request</p>-->
                      <!--        <h3 class="rate-percentage">{{ $toal_pending_req }}</h3>-->
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>68.8</span></p>-->
                      <!--      </div>-->
                      <!--      <div class="d-none d-md-block">-->
                      <!--        <p class="statistics-title">OD Transfer</p>-->
                      <!--        <h3 class="rate-percentage">{{ $toal_od }}</h3>-->
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-down"></i><span>+0.8%</span></p>-->
                      <!--      </div>-->
                      <!--      <div class="d-none d-md-block">-->
                      <!--        <p class="statistics-title">AEPS Txn</p>-->
                      <!--        <h3 class="rate-percentage">0</h3>-->
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>68.8</span></p>-->
                      <!--      </div>-->
                      <!--      <div class="d-none d-md-block">-->
                      <!--        <p class="statistics-title">Other Txn</p>-->
                      <!--        <h3 class="rate-percentage">0</h3>-->
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-down"></i><span>+0.8%</span></p>-->
                      <!--      </div>-->
                      <!--    </div>-->
                      <!--  </div>-->
                      <!--</div>-->
                      <!-- Service Cards -->
                      <div class="row">
                          <!-- TOP -->  
                         <div class="col-lg-12 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-top-section">
                                    <div class="card-body">
                                        <!--<h4 class="card-title card-title-dash mb-4">Money Transfer Vol</h4>-->
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Wallet Balance</p>
                                              <h4 class="mb-0 fw-bold">{{ Auth::user()->wallet->balanceFloat }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Total Retailers</p>
                                              <h4 class="mb-0 fw-bold">{{ $total_retailer }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Request</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">OD Transfer</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_od }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          <div class="col-md-2 grid-margin stretch-card">
                            <div class="card bg-fund-section">
                              <div class="card-body">
                                <h4 class="card-title">Load Fund Online</h4>
                                <!--<p class="card-description"> Bordered layout </p>-->
                                <form class="forms-sample" action="{{ route('airpay_payment_retailer') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                  <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required="">
                                  </div>
                                  <div class="form-group">
                                    <label for="name">Surname</label>
                                    <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" value="{{ old('surname') }}" required="">
                                  </div>
                                  <div class="form-group">
                                    <label for="name">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required="">
                                  </div>
                                  <div class="form-group">
                                    <label for="name">Mobile</label>
                                    <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile" value="{{ old('mobile') }}" required="">
                                  </div>
                                  
                                  <div class="form-group">
                                    <label for="name" >Selfie</label>
                                    <input type="file" name="selfie" class="file-upload-default" accept="image/*">
                                    <div class="input-group col-xs-12">
                                      <input type="text" class="form-control file-upload-info" disabled placeholder="Selfie">
                                      <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="height: 100%;color:white">Upload</button>
                                      </span>
                                    </div>
                                  </div>
                                  
                                  <div class="form-group">
                                      <label for="name" >Method</label>
                                        <select class="js-example-basic-single w-100 form-control" id="method" name="method" required="">
                                               <option value="ppc">Prepaid card</option>
                                               <option value="pg">Payment gateway</option>
                                               <option value="nb">Netbanking</option>
                                               <option value="pgcc">Credit card</option>
                                               <option value="pgdc">Debit card</option>
                                               <option value="upi">UPI</option>
                                               <option value="btqr">Bharat QR</option>
                                               <option value="payltr">Pay later</option>
                                               <option value="emi">EMI</option>
                                        </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="{{ old('amount') }}" required="">
                                  </div>
                                  
                                  <div class="button-container">
                                    <button type="submit" class="btn btn-primary btn-lg text-white mb-0 me-0 btn-rounded">Load Online</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>
                          
                          <div class="col-lg-10 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-fund-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Fund Request</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Total Request</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Request</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Accept Request</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Reject Request</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                         <!-- DMT -->  
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-DMT-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Vol</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs Till Date</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-DMT-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-scan-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-scan-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Txn</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today No. Of Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs Till Date</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                         
                         <!-- SCAN n PAY -->  
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-DMT-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">SCAN n PAY Vol</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs Till Date</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                         
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-DMT-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">SCAN n PAY Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Count</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-scan-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">SCAN n PAY Txn</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today No. Of Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month Txn</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs Till Date</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        
                         <div class="col-lg-6 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-scan-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">SCAN n PAY Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_pending_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_accept_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $toal_reject_req }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        
                         <div class="col-lg-12 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-chart-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Commission</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Money Transfer</p>
                                              <h4 class="mb-0 fw-bold">0</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">AEPS</p>
                                              <h4 class="mb-0 fw-bold">0</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">CMS</p>
                                              <h4 class="mb-0 fw-bold">0</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Scan N Pay</p>
                                              <h4 class="mb-0 fw-bold">0</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                         
                         
                          
                          <div class="col-lg-4 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                  <div class="card card-rounded bg-chart-section">
                                    <div class="card-body">
                                      <div class="row">
                                        <div class="col-lg-12">
                                          <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="card-title card-title-dash">Fund Transfer</h4>
                                          </div>
                                          <div>
                                            <canvas class="my-auto" id="doughnutChart"></canvas>
                                          </div>
                                          <div id="doughnutChart-legend" class="mt-5 text-center"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                          </div>
                          
                          <div class="col-8 grid-margin stretch-card">
                              <div class="card card-rounded bg-chart-section">
                                <div class="card-body">
                                  <div class="d-sm-flex justify-content-between align-items-start">
                                    <div>
                                      <h4 class="card-title card-title-dash">Money Transfer</h4>
                                      <!--<p class="card-subtitle card-subtitle-dash">Lorem ipsum dolor sit amet consectetur adipisicing elit</p>-->
                                    </div>
                                    
                                  </div>
                                  <div class="d-sm-flex align-items-center mt-1 justify-content-between">
                                    <div class="d-sm-flex align-items-center mt-4 justify-content-between">
                                      <h2 class="me-2 fw-bold">₹{{ $thisweek_dmt_total }}</h2>
                                      <h4 class="me-2">INR</h4>
                                      <!--<h4 class="text-success">(+1.37%)</h4>-->
                                    </div>
                                    <div class="me-3">
                                      <div id="marketingOverview-legend"></div>
                                    </div>
                                  </div>
                                  <div class="chartjs-bar-wrapper mt-3">
                                    <canvas id="marketingOverview"></canvas>
                                  </div>
                                </div>
                              </div>
                            </div>
                      </div>
                      <!-- Service Cards end -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
@endsection

@section('page-script')

<script>
    
    function getTxnId() {
        var state = $("#states").val();
        $.ajax({
            type: 'get',
            dataType:'text',
            url: "{{ route('get_treansaction_id') }}",
            data: {"state" : state ,"_token":"{{ csrf_token() }}"},
            success: function (result) {
                return result;
            }
        });
    }
    
    if ($("#doughnutChart").length) { 
      const doughnutChartCanvas = document.getElementById('doughnutChart');
      new Chart(doughnutChartCanvas, {
        type: 'doughnut',
        data: {
          labels: ['OD Fund','Fund Request'],
          datasets: [{
            data: [100, 500],
            backgroundColor: [
              "#1F3BB3",
              "#FDD0C7"
            ],
            borderColor: [
              "#1F3BB3",
              "#FDD0C7"
            ],
          }]
        },
        options: {
          cutout: 90,
          animationEasing: "easeOutBounce",
          animateRotate: true,
          animateScale: false,
          responsive: true,
          maintainAspectRatio: true,
          showScale: true,
          legend: false,
          plugins: {
            legend: {
                display: false,
            }
          }
        },
        plugins: [{
          afterDatasetUpdate: function (chart, args, options) {
              const chartId = chart.canvas.id;
              var i;
              const legendId = `${chartId}-legend`;
              const ul = document.createElement('ul');
              for(i=0;i<chart.data.datasets[0].data.length; i++) {
                  ul.innerHTML += `
                  <li>
                    <span style="background-color: ${chart.data.datasets[0].backgroundColor[i]}"></span>
                    ${chart.data.labels[i]}
                  </li>
                `;
              }
              return document.getElementById(legendId).appendChild(ul);
            }
        }]
      });
    }

    if ($("#marketingOverview").length) { 
      const marketingOverviewCanvas = document.getElementById('marketingOverview');
      new Chart(marketingOverviewCanvas, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($lastWeekday); ?>,
          datasets: [{
            label: 'Last week',
            data: <?php echo json_encode($lastWeekTransactions); ?>,
            backgroundColor: "#52CDFF",
            borderColor: [
                '#52CDFF',
            ],
              borderWidth: 0,
              barPercentage: 0.35,
              fill: true, // 3: no fill
              
          },{
            label: 'This week',
            data: <?php echo json_encode($currentWeekTransactions); ?>,
            backgroundColor: "#1F3BB3",
            borderColor: [
                '#1F3BB3',
            ],
            borderWidth: 0,
              barPercentage: 0.35,
              fill: true, // 3: no fill
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          elements: {
            line: {
                tension: 0.4,
            }
        },
        
          scales: {
            y: {
              border: {
                display: false
              },
              grid: {
                display: true,
                drawTicks: false,
                color:"#F0F0F0",
                zeroLineColor: '#F0F0F0',
              },
              ticks: {
                beginAtZero: false,
                autoSkip: true,
                maxTicksLimit: 4,
                color:"#6B778C",
                font: {
                  size: 10,
                }
              }
            },
            x: {
              border: {
                display: false
              },
              stacked: true,
              grid: {
                display: false,
                drawTicks: false,
              },
              ticks: {
                beginAtZero: false,
                autoSkip: true,
                maxTicksLimit: 7,
                color:"#6B778C",
                font: {
                  size: 10,
                }
              }
            }
          },
          plugins: {
            legend: {
                display: false,
            }
          }
        },
        plugins: [{
          afterDatasetUpdate: function (chart, args, options) {
              const chartId = chart.canvas.id;
              var i;
              const legendId = `${chartId}-legend`;
              const ul = document.createElement('ul');
              for(i=0;i<chart.data.datasets.length; i++) {
                  ul.innerHTML += `
                  <li>
                    <span style="background-color: ${chart.data.datasets[i].borderColor}"></span>
                    ${chart.data.datasets[i].label}
                  </li>
                `;
              }
              return document.getElementById(legendId).appendChild(ul);
            }
        }]
      });
    }
</script>
@endsection
