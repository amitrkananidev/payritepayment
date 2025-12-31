        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('dashboard_retailer') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
              </a>
            </li>
            <li class="nav-item nav-category">Services</li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#Services" aria-expanded="false" aria-controls="Services">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Services</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="Services">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('get_sender_retailer') }}">CC PAYOUT</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('credo_aeps_retailer') }}">AEPS</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('dmt_login_retailer') }}">DMT</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('bill_dmt_login_retailer') }}">DMT 2</a></li>
                  <?php $users = [9825615077,9638971671,9898789944,8141615959,9898061583,9978177178]; ?>
                  
                  <li class="nav-item"> <a class="nav-link" href="{{ route('ace_dmt_login_retailer') }}">DMT 3</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('digi_dmt_login_retailer') }}">DIGIKHATA</a></li>
                 
                  <li class="nav-item"> <a class="nav-link" href="{{ route('upi_transfer_retailer') }}">UPI</a></li>
                  <!--<li class="nav-item"> <a class="nav-link" href="{{ route('payout_retailer') }}">PAYOUT</a></li>-->
                  
                  <li class="nav-item"> <a class="nav-link" href="{{ route('get_recharge_retailer') }}">Prepaid</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('get_dth_recharge_retailer') }}">DTH</a></li>
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
                    <li class="nav-item"> <a class="nav-link" href="{{ route('business_report_retailer') }}">Business Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('aeps_report_retailer') }}">AEPS Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('dmt_report_retailer') }}">DMT Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('scannpay_report_retailer') }}">Scan N Pay Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('online_fund_retailer') }}">PG Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('qr_fund_retailer') }}">QR Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_request_retailer') }}">Fund Request</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('my_statment_retailer') }}">Statment</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('fund_od_report_retailer') }}">OD Report</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('recharge_report_retailer') }}">Recharge</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('ccpayout_report_retailer') }}">CC PAYOUT</a></li>
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
                  <li class="nav-item"> <a class="nav-link" href="{{ route('create_fund_request_retailer') }}">Create</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('online_fund_retailer') }}">Online Fund</a></li>
                </ul>
              </div>
            </li>
            
            
            
          </ul>
        </nav>