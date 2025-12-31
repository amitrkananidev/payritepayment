@extends('new_layouts/app')

@section('title', 'Dashboard')

@section('page-style')
<style>
#marketingOverview-aeps-legend ul:first-child {
    display: flex;
}
#marketingOverview-aeps-legend ul {
    margin-bottom: 0;
    display: none;
    list-style-type: none;
}
#marketingOverview-aeps-legend ul li {
    list-style: none;
    color: #737F8B;
    font-size: 12px;
    display: inline-block;
    margin-left: 1rem;
}
#marketingOverview-aeps-legend ul li span {
    width: 10px;
    height: 10px;
    border-radius: 100%;
    display: inline-block;
    margin-right: 10px;
}
</style>
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
                      <!--    <div class="statistics-details d-flex align-items-center justify-content-between bg-primary">-->
                      <!--      <div class="bg-primary py-2 px-2">-->
                      <!--        <p class="statistics-title text-white">Wallet Balance</p>-->
                      <!--        <h3 class="rate-percentage">{{ Auth::user()->wallet->balanceFloat }}</h3>-->
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>-0.5%</span></p>-->
                      <!--      </div>-->
                      <!--      <div>-->
                      <!--        <p class="statistics-title">Pending Request</p>-->
                      <!--        <h3 class="rate-percentage">0</h3>-->
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+0.1%</span></p>-->
                      <!--      </div>-->
                      <!--      <div>-->
                      <!--        <p class="statistics-title">BBPS</p>-->
                      <!--        <h3 class="rate-percentage">0</h3>-->
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>68.8</span></p>-->
                      <!--      </div>-->
                      <!--      <div class="d-none d-md-block">-->
                      <!--        <p class="statistics-title">DMT Txn</p>-->
                      <!--        <h3 class="rate-percentage">{{ $data['today_total_transactions_dmt'] }}</h3>-->
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
                         <!--<div class="col-lg-12 d-flex flex-column">-->
                         <!--     <div class="row flex-grow">-->
                         <!--       <div class="col-md-6 col-lg-12 grid-margin stretch-card">-->
                         <!--         <div class="card card-rounded emboss bg-top-section">-->
                         <!--           <div class="card-body">-->
                                        <!--<h4 class="card-title card-title-dash mb-4">Money Transfer Vol</h4>-->
                         <!--             <div class="row">-->
                                          
                         <!--               <div class="col-lg-3">-->
                         <!--                 <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">-->
                         <!--                   <div>-->
                         <!--                     <p class="text-small mb-2">Wallet Balance</p>-->
                         <!--                     <h4 class="mb-0 fw-bold">{{ Auth::user()->wallet->balanceFloat }}</h4>-->
                         <!--                   </div>-->
                         <!--                 </div>-->
                         <!--               </div>-->
                                        
                                        <!--<div class="col-lg-3">-->
                                        <!--  <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">-->
                                        <!--    <div>-->
                                        <!--      <p class="text-small mb-2">Total Retailers</p>-->
                                        <!--      <h4 class="mb-0 fw-bold">0</h4>-->
                                        <!--    </div>-->
                                        <!--  </div>-->
                                        <!--</div>-->
                                        
                         <!--               <div class="col-lg-3">-->
                         <!--                 <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">-->
                         <!--                   <div>-->
                         <!--                     <p class="text-small mb-2">Pending Request</p>-->
                         <!--                     <h4 class="mb-0 fw-bold">0</h4>-->
                         <!--                   </div>-->
                         <!--                 </div>-->
                         <!--               </div>-->
                                        
                         <!--               <div class="col-lg-3">-->
                         <!--                 <div class="d-flex justify-content-between align-items-center">-->
                         <!--                   <div>-->
                         <!--                     <p class="text-small mb-2"></p>-->
                         <!--                     <h4 class="mb-0 fw-bold"></h4>-->
                         <!--                   </div>-->
                         <!--                 </div>-->
                         <!--               </div>-->
                                        
                         <!--             </div>-->
                         <!--           </div>-->
                         <!--         </div>-->
                         <!--       </div>-->
                         <!--     </div>-->
                         <!--   </div>-->
                            
                          <div class="col-md-3 grid-margin stretch-card">
                            <div class="card emboss bg-fund-section">
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
                                    <center><button type="submit" class="btn btn-danger btn-lg text-white mb-0 me-0 btn-rounded">Load Online</button></center>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-9 grid-margin">
                            <!-- DMT -->  
                         <div class="col-lg-12 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-DMT-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Vol</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_total_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['mst_total_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_total_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs MST</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_vs_this_month'] }}</h4>
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
                                  <div class="card card-rounded emboss bg-DMT-section-dark">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Money Transfer Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_success_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_pending_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_failed_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_failed_transactions_dmt'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        
                        
                        <!-- Scan And Pay -->  
                         <div class="col-lg-12 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-scan-section-dark">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Scan And Pay Vol</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_total_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['mst_total_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_total_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs MST</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_vs_this_month_upi'] }}</h4>
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
                                  <div class="card card-rounded emboss bg-scan-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">Scan And Pay Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_success_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Pending Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_pending_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_failed_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Refund Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_failed_transactions_upi'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                    
                    <!-- AEPS -->  
                         <div class="col-lg-12 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-aeps-section-dark">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">AEPS Vol</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Today Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_total_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">MST Vol</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['mst_total_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Last Month</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_total_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Last Month Vs MST</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['last_month_vs_this_month_aeps'] }}</h4>
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
                                  <div class="card card-rounded emboss bg-aeps-section">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash mb-4">AEPS Status</h4>
                                      <div class="row">
                                          
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Success Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_success_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Failed Amount</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_failed_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                            <div>
                                              <p class="text-small mb-2">Balance Enquiry</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_BE_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                              <p class="text-small mb-2">Mini Statement</p>
                                              <h4 class="mb-0 fw-bold">{{ $data['today_MS_transactions_aeps'] }}</h4>
                                            </div>
                                          </div>
                                        </div>
                                        
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                              
                              
                          </div>
                          <div class="col-md-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-fund-section">
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
                              
                              <div class="col-md-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-fund-section">
                                    <div class="card-body">
                                      <div class="d-sm-flex justify-content-between align-items-start">
                                        <div>
                                          <h4 class="card-title card-title-dash">AEPS</h4>
                                          <!--<p class="card-subtitle card-subtitle-dash">Lorem ipsum dolor sit amet consectetur adipisicing elit</p>-->
                                        </div>
                                        
                                      </div>
                                      <div class="d-sm-flex align-items-center mt-1 justify-content-between">
                                        <div class="d-sm-flex align-items-center mt-4 justify-content-between">
                                          <h2 class="me-2 fw-bold">₹{{ $thisweek_aeps_total }}</h2>
                                          <h4 class="me-2">INR</h4>
                                          <!--<h4 class="text-success">(+1.37%)</h4>-->
                                        </div>
                                        <div class="me-3">
                                          <div id="marketingOverview-aeps-legend"></div>
                                        </div>
                                      </div>
                                      <div class="chartjs-bar-wrapper mt-3">
                                        <canvas id="marketingOverview-aeps"></canvas>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                          
                          
                        
                         
                            
                         
                            
                          <div class="col-lg-4 d-flex flex-column">
                              <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-chart-section ">
                                    <div class="card-body">
                                      <div class="row">
                                        <div class="col-lg-12">
                                          <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="card-title card-title-dash">Serviices</h4>
                                          </div>
                                          <div>
                                            <canvas class="my-auto" id="doughnutChart"></canvas>
                                          </div>
                                          <div id="doughnutChart-legend" class="mt-5 text-center text-white"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                          </div>
                          <div class="col-lg-4 d-flex flex-column">
                            <div class="row flex-grow">
                                <div class="col-md-12 col-lg-12 grid-margin stretch-card">
                                  <div class="card card-rounded emboss bg-chart-section">
                                    <div class="card-body card-rounded">
                                      <h4 class="card-title  card-title-dash">Recent Notification</h4>
                                      <div class="list align-items-center border-bottom py-2">
                                        <div class="wrapper w-100">
                                          <p class="mb-2 fw-medium">AEPS temporary down </p>
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                              <i class="mdi mdi-calendar text-muted me-1"></i>
                                              <p class="mb-0 text-small text-muted">Mar 28, 2024</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="list align-items-center border-bottom py-2">
                                        <div class="wrapper w-100">
                                          <p class="mb-2 fw-medium"> Money Transfer service working fine. </p>
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                              <i class="mdi mdi-calendar text-muted me-1"></i>
                                              <p class="mb-0 text-small text-muted">Mar 28, 2024</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="list align-items-center border-bottom py-2">
                                        <div class="wrapper w-100">
                                          <p class="mb-2 fw-medium"> Online Load Service enable </p>
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                              <i class="mdi mdi-calendar text-muted me-1"></i>
                                              <p class="mb-0 text-small text-muted">Mar 28, 2024</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="list align-items-center border-bottom py-2">
                                        <div class="wrapper w-100">
                                          <p class="mb-2 fw-medium"> BBPS now a time disable </p>
                                          <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                              <i class="mdi mdi-calendar text-muted me-1"></i>
                                              <p class="mb-0 text-small text-muted">Mar 28, 2024</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="list align-items-center pt-3">
                                        <div class="wrapper w-100">
                                          <p class="mb-0">
                                            <!--<a href="#" class="fw-bold text-primary">Show all <i class="mdi mdi-arrow-right ms-2"></i></a>-->
                                          </p>
                                        </div>
                                      </div>
                                    </div>
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
          labels: ['DMT','AEPS','BBPS','OTHER'],
          datasets: [{
            data: [{{ $data['mst_total_transactions_dmt'] }}, {{ $data['total_transactions_aeps'] }}, 30, 10],
            backgroundColor: [
              "#1F3BB3",
              "#FDD0C7",
              "#52CDFF",
              "#81DADA"
            ],
            borderColor: [
              "#1F3BB3",
              "#FDD0C7",
              "#52CDFF",
              "#81DADA"
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
    
    if ($("#marketingOverview-aeps").length) { 
      const marketingOverviewCanvas = document.getElementById('marketingOverview-aeps');
      new Chart(marketingOverviewCanvas, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($lastWeekday_aeps); ?>,
          datasets: [{
            label: 'Last week',
            data: <?php echo json_encode($lastWeekTransactions_aeps); ?>,
            backgroundColor: "#52CDFF",
            borderColor: [
                '#52CDFF',
            ],
              borderWidth: 0,
              barPercentage: 0.35,
              fill: true, // 3: no fill
              
          },{
            label: 'This week',
            data: <?php echo json_encode($currentWeekTransactions_aeps); ?>,
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
