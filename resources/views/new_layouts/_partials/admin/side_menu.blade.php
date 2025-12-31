        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('dashboard_admin') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
              </a>
            </li>
            <li class="nav-item nav-category">Retailer</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#retailers" aria-expanded="false" aria-controls="retailers">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Retailer</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="retailers">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('create_retailer') }}">Create Retailer</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('view_retailer') }}">View</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('services_retailer') }}">Services</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item nav-category">Distributor</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#distributor" aria-expanded="false" aria-controls="distributor">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Distributor</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="distributor">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('create_distributor') }}">Create Distributor</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('view_distributor') }}">View</a></li>
                </ul>
              </div>
            </li>
            
            <li class="nav-item nav-category">Fund Manage</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#fundrequest" aria-expanded="false" aria-controls="fundrequest">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Fund Manage</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="fundrequest">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_request') }}">Pending</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_od_report') }}">OD Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('add_bank_fund') }}">Add Bank</a></li>
                </ul>
              </div>
            </li>
            
            <li class="nav-item nav-category">Report</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#report" aria-expanded="false" aria-controls="report">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Report</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="report">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('business_report') }}">Business Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('consolidated_data') }}">Consolidated Report</a></li>
                    <li class="nav-item"> <a class="nav-link" href="{{ route('aeps_report') }}">AEPS Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_request_report') }}">Fund Req Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_od_report') }}">OD Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('dmt_report') }}">DMT Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('get_gst') }}">GST Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('get_tds') }}">TDS Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('total_tds_data') }}">Total TDS Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('online_fund') }}">PG Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('qr_fund') }}">QR Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('commission_fee') }}">Commission And Fee</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('all_users_wallet') }}">User Wallet Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('account_verify') }}">Account Verification</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('recharge_report') }}">Recharge</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('ccpayout_report') }}">CCPAYOUT</a></li>
                </ul>
              </div>
            </li>
            
            
            <!--<li class="nav-item nav-category">UI Elements</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">-->
            <!--    <i class="menu-icon mdi mdi-floor-plan"></i>-->
            <!--    <span class="menu-title">UI Elements</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="ui-basic">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/ui-features/buttons.html">Buttons</a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/ui-features/dropdowns.html">Dropdowns</a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/ui-features/typography.html">Typography</a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">-->
            <!--    <i class="menu-icon mdi mdi-card-text-outline"></i>-->
            <!--    <span class="menu-title">Form elements</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="form-elements">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"><a class="nav-link" href="pages/forms/basic_elements.html">Basic Elements</a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">-->
            <!--    <i class="menu-icon mdi mdi-chart-line"></i>-->
            <!--    <span class="menu-title">Charts</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="charts">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/charts/chartjs.html">ChartJs</a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">-->
            <!--    <i class="menu-icon mdi mdi-table"></i>-->
            <!--    <span class="menu-title">Tables</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="tables">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/tables/basic-table.html">Basic table</a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">-->
            <!--    <i class="menu-icon mdi mdi-layers-outline"></i>-->
            <!--    <span class="menu-title">Icons</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="icons">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/icons/font-awesome.html">Font Awesome</a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">-->
            <!--    <i class="menu-icon mdi mdi-account-circle-outline"></i>-->
            <!--    <span class="menu-title">User Pages</span>-->
            <!--    <i class="menu-arrow"></i>-->
            <!--  </a>-->
            <!--  <div class="collapse" id="auth">-->
            <!--    <ul class="nav flex-column sub-menu">-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/samples/blank-page.html"> Blank Page </a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> 404 </a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/samples/error-500.html"> 500 </a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/samples/login.html"> Login </a></li>-->
            <!--      <li class="nav-item"> <a class="nav-link" href="pages/samples/register.html"> Register </a></li>-->
            <!--    </ul>-->
            <!--  </div>-->
            <!--</li>-->
            <!--<li class="nav-item">-->
            <!--  <a class="nav-link" href="docs/documentation.html">-->
            <!--    <i class="menu-icon mdi mdi-file-document"></i>-->
            <!--    <span class="menu-title">Documentation</span>-->
            <!--  </a>-->
            <!--</li>-->
          </ul>
        </nav>