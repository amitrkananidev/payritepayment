        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('dashboard_distributor') }}">
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
                  <li class="nav-item"> <a class="nav-link" href="{{ route('create_retailer_distributor') }}">Create Retailer</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('view_retailer_distributor') }}">View</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('rekyc_distributor') }}">ReKyc</a></li>
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
                  <li class="nav-item"> <a class="nav-link" href="{{ route('create_fund_request_distributor') }}">Create</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_od_report_distributor') }}">OD</a></li>
                  <!--<li class="nav-item"> <a class="nav-link" href="{{ route('add_bank_fund_distributor') }}">Add Bank</a></li>-->
                </ul>
              </div>
            </li>
            
            <li class="nav-item nav-category">Report</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#reports" aria-expanded="false" aria-controls="reports">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Report</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="reports">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('my_statment_distributor') }}">A/C Statment</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('dmt_report_distributor') }}">DMT</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('scannpay_report_distributor') }}">Scan And Pay</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('retailer_fund_request_distributor') }}">Ret. Fund Request</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_request_distributor') }}">My Fund Request</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_od_report_distributor') }}">OD (Credit)</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('online_fund_distributor') }}">PG Report</a></li>
                </ul>
              </div>
            </li>
            
            
            
          </ul>
        </nav>