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
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="statistics-details d-flex align-items-center justify-content-between">
                            <div>
                              <p class="statistics-title">Total Dist</p>
                              <h3 class="rate-percentage">{{ $total_dist }}</h3>
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>-0.5%</span></p>-->
                            </div>
                            <div>
                              <p class="statistics-title">Total Retailer</p>
                              <h3 class="rate-percentage">{{ $total_retailer }}</h3>
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+0.1%</span></p>-->
                            </div>
                            <div>
                              <p class="statistics-title">Pending Req.</p>
                              <h3 class="rate-percentage">{{ $total_fund_requests }}</h3>
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>68.8</span></p>-->
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">DMT Txn</p>
                              <h3 class="rate-percentage">{{ $total_transactions_dmt }}</h3>
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-down"></i><span>+0.8%</span></p>-->
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">AEPS Txn</p>
                              <h3 class="rate-percentage">{{ $total_transactions_aeps }}</h3>
                              <!--<p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>68.8</span></p>-->
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">Online Load</p>
                              <h3 class="rate-percentage">{{ $total_transactions_online }}</h3>
                              <!--<p class="text-success d-flex"><i class="mdi mdi-menu-down"></i><span>+0.8%</span></p>-->
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- Service Cards -->
                      <div class="row">
                        <!-- DMT Service Cards -->  
                        <div class="col-lg-4 d-flex flex-column">
                          <div class="row flex-grow">
                            <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                    <h4 class="card-title card-title-dash mb-4">Money Transfer</h4>
                                  <div class="row">
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                        
                                        <div>
                                          <p class="text-small mb-2">Today Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $today_transactions_dmt }}</h4>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center">
                                        
                                        <div>
                                          <p class="text-small mb-2">Month Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $month_transactions_dmt }}</h4>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- AEPS Service Cards -->  
                        <div class="col-lg-4 d-flex flex-column">
                          <div class="row flex-grow">
                            
                            <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                <h4 class="card-title card-title-dash mb-4">AEPS</h4>
                                  <div class="row">
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                        
                                        <div>
                                          <p class="text-small mb-2">Today Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $today_transactions_aeps }}</h4>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center">
                                        
                                        <div>
                                          <p class="text-small mb-2">Month Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $month_transactions_aeps }}</h4>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- QR Service Cards -->  
                        <div class="col-lg-4 d-flex flex-column">
                          <div class="row flex-grow">
                            
                            <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                <h4 class="card-title card-title-dash mb-4">UPI-QR</h4>
                                  <div class="row">
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">
                                        
                                        <div>
                                          <p class="text-small mb-2">Today Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $today_transactions_qr }}</h4>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-6">
                                      <div class="d-flex justify-content-between align-items-center">
                                        
                                        <div>
                                          <p class="text-small mb-2">Month Volume</p>
                                          <h4 class="mb-0 fw-bold">{{ $month_transactions_qr }}</h4>
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
                      <!-- Service Cards end -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
@endsection

@section('page-script')

@endsection
