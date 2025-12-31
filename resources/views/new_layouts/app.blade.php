<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PAYRITE | @yield('title') </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/select.dataTables.min.css') }}">
    
    <link rel="stylesheet" href="{{ asset('assets/vendors/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Include SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <style>
        body, html{
            background-image: url("{{ asset('bg.jpg') }}");
        }
        .hide{
            display: none;
        }
        .navbar{
            background: transparent;
        }
        
        @media (max-width: 991px) {
            .navbar{
                background: #5952ac !important;
            }
        }
        .card, .card.card-rounded{
            box-shadow: -7px -7px 16px 0 #FFFFFF, 7px 7px 10px -4px rgba(116,150,179,0.27);
          }
        
        .emboss {
          width: 100%;
          
          background: #EDF3F9;
          box-shadow: -7px -7px 16px 0 #FFFFFF, 7px 7px 10px -4px rgba(116,150,179,0.27);
          border-radius: 18px;  
          
        }
        .sidebar{
            border-radius: 0px 20px 20px 0px;
            background: #5952AC;
        }
        .navbar .navbar-menu-wrapper{
            border-radius: 0px 0px 20px 20px;
            background: #5952AC;
            color: #fff;
        }
        .navbar .navbar-menu-wrapper .navbar-nav .nav-item .welcome-text,.sidebar .nav .nav-item.nav-category,.sidebar .nav .nav-item .nav-link,.sidebar .nav .nav-item .nav-link i.menu-icon{
            color: #fff;
        }
        .sidebar .nav .nav-item:hover > .nav-link i{
            color: var(--bs-nav-link-color);
        }
        .content-wrapper,.footer{
            background: #f4f5f7b8;
        }
        
        .bg-top-section{
            background: #6fc15b !important;
        }
        
        .bg-fund-section{
            background: #BCFF79 !important;
        }
        
        .bg-DMT-section{
            background: #F6C08E !important;
        }
        .bg-DMT-section-dark{
            background: #F6C08E !important;
        }
        
        .bg-scan-section{
            background: #FFC1C2 !important;
        }
        .bg-scan-section-dark{
            background: #FFC1C2 !important;
        }
        
        .bg-aeps-section{
            background: #ffc1e9 !important;
        }
        .bg-aeps-section-dark{
            background: #ffc1e9 !important;
        }
        
        .bg-chart-section{
            background: #dcc1f5 !important;
        }
        
        .bg-inner-page{
            background: #006BBE !important;
        }
        
        .bg-inner-page-dark{
            background: #094f87 !important;
        }
        
        .bg-red-section{
            background: #F6C08E !important;
        }
        
        .bg-bbps-inner-page{
            background: #FFF !important;
        }
        
        /* Loader styles */
        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 99999;
        }

        .loader {
            position: relative;
            width: 80px;
            height: 80px;
        }

        /* Spinner loader */
        .spinner {
            position: absolute;
            width: 80px;
            height: 80px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Pulse loader */
        .pulse {
            position: absolute;
            width: 80px;
            height: 80px;
            background-color: #3498db;
            border-radius: 50%;
            opacity: 0.6;
            animation: pulse 1.5s ease-out infinite;
        }

        /* Loading text */
        .loading-text {
            margin-top: 120px;
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        /* Buttons */
        .button-container {
            margin-top: 2rem;
        }

        button {
            padding: 12px 24px;
            margin: 0 10px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Animations */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.6;
            }
            50% {
                transform: scale(1);
                opacity: 0.3;
            }
            100% {
                transform: scale(0.8);
                opacity: 0.6;
            }
        }
    </style>
    <style>
    #amountdiv {
      position: absolute;
      z-index: 9999999;
      text-align: center;
      left: 50%; /* Align to the center of the screen */
  transform: translateX(-50%); /* Offset to ensure it's centered */
    
    }
    
    #mydivheader {
      padding: 10px;
      cursor: move;
      z-index: 10;
      background-color: #6fc15b;
      color: #fff;
      box-shadow: -7px -7px 16px 0 #FFFFFF, 7px 7px 10px -4px rgba(116, 150, 179, 0.27);
      border-radius: 18px;
    }
    </style>
    @yield('page-style')
  </head>
  <body class="with-welcome-text">
    <div id="amountdiv" class="">
      <div id="mydivheader">₹ {{ Auth::user()->wallet->balanceFloat }} <i class="menu-icon mdi mdi-qrcode-scan" data-bs-toggle="modal" data-bs-target="#qr_cust_detail"></i></div>
    </div>
    <!-- Loader -->
    <div class="loader-container" id="loaderContainer" style="display: none;">
        <div class="loader" id="loader">
            <div class="spinner" id="spinner"></div>
            <div class="pulse" id="pulse" style="display: none;"></div>
        </div>
    </div>
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
              <span class="icon-menu"></span>
            </button>
          </div>
          <div>
            <a class="navbar-brand brand-logo" href="{{ route('main-page') }}">
              <img src="{{ asset('assets/images/Payrite_Logo.png') }}" alt="logo" />
            </a>
            <a class="navbar-brand brand-logo-mini" id="UserDropdownMobile" href="#" data-bs-toggle="dropdown" aria-expanded="false">
              <img class="img-md rounded-circle" src="{{ asset('assets/images/faces/face8.jpg') }}" alt="logo" />
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdownMobile">
                <!--<a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a>-->
                <a class="dropdown-item" href="{{ route('change_password') }}"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Change Password</a>
                <a class="dropdown-item" href="{{ route('logout') }}"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out !</a>
                @if($pdfavailable)
                <a class="dropdown-item" download  href="https://user.payritepayment.in/upload/tds/{{ $pdffile }}"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Download TDS 24-25</a>
                @endif
              </div>
          </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-top">
          <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
              <h1 class="welcome-text" ><span id="greeting">Good Morning</span>, <span class="text-white fw-bold">{{ Auth::user()->name }}</span></h1>
              <!--<h3 class="welcome-sub-text">Your performance summary this week </h3>-->
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            
            
            
            
            
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
              <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle" src="{{ asset('assets/images/faces/face8.jpg') }}" alt="Profile image"> </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="{{ asset('assets/images/faces/face8.jpg') }}" alt="Profile image">
                  <p class="mb-1 mt-3 fw-semibold">{{ Auth::user()->name }} {{ Auth::user()->surname }}</p>
                  <p class="fw-light text-muted mb-0">{{ Auth::user()->mobile }}</p>
                </div>
                <!--<a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a>-->
                <!--<a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages</a>-->
                <!--<a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Activity</a>-->
                <a class="dropdown-item" href="{{ route('change_password') }}"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Change Password</a>
                <a class="dropdown-item" href="{{ route('logout') }}"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out</a>
                @if($pdfavailable)
                <a class="dropdown-item" download  href="https://user.payritepayment.in/uploads/tds/{{ $pdffile }}"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Download TDS 24-25</a>
                @endif
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        
        @include('new_layouts/_partials/'.Auth::user()->user_folder.'/side_menu')
        
        
        <!-- partial -->
        <div class="main-panel">
          @yield('content')
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block"> <a href="" target="_blank"></a></span>
              <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright © 2024. All rights reserved.</span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    @include('new_layouts.inc.qr')
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/typeahead.js/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('assets/js/file-upload.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    
    <!-- Include SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    //Make the DIV element draggagle:
    dragElement(document.getElementById("amountdiv"));
    
    function dragElement(elmnt) {
      var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
      if (document.getElementById(elmnt.id)) {
        /* if present, the header is where you move the DIV from:*/
        document.getElementById(elmnt.id).onmousedown = dragMouseDown;
      } else {
        /* otherwise, move the DIV from anywhere inside the DIV:*/
        elmnt.onmousedown = dragMouseDown;
      }
    
      function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
      }
    
      function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
      }
    
      function closeDragElement() {
        /* stop moving when mouse button is released:*/
        document.onmouseup = null;
        document.onmousemove = null;
      }
    }
    </script>
    <!-- endinject -->
    <script>
        
        @if(Session::has('error'))
        Swal.fire({
          title: 'Error!',
          text: '{{ Session::get("error") }}',
          icon: 'error'
        });
        @endif
        
        @if(Session::has('success'))
        Swal.fire({
          title: 'Success!',
          text: '{{ Session::get("success") }}',
          icon: 'success'
        });
        @endif
        
        @if(Session::has('dmt_success'))
        Swal.fire({
          title: 'Success!',
          text: '{{ Session::get("dmt_success") }}',
          icon: 'success',
          showCancelButton: true,
          confirmButtonText: "OK",
          cancelButtonText: "Receipt"
            }).then((result) => {
              if (result.isDismissed) {
                // If the user clicks the "Redirect" button
                window.open("https://user.payritepayment.in/retailer-receipt/dmt/{{ Session::get('dmt_transaction_id') }}", "_blank");
                 // Replace with the URL you want to redirect to
            }
        });
        @endif
        
        $(document).ready(function() {
            // Get the current date and time
            var currentDateTime = new Date();
            // Get the current hour
            var currentHour = currentDateTime.getHours();

            // Variable to hold the greeting message
            var greetingMessage;

            // Determine the appropriate greeting message based on the current hour
            if (currentHour < 12) {
                greetingMessage = "Good Morning";
            } else if (currentHour >= 12 && currentHour < 17) {
                greetingMessage = "Good Afternoon";
            } else if (currentHour >= 17 && currentHour < 20) {
                greetingMessage = "Good Evening";
            } else {
                greetingMessage = "Good Night";
            }

            // Display the greeting message in the #greeting element
            $("#greeting").text(greetingMessage);
        });
        
        function convertTimestamp(timestamp) {
            // Parse the input timestamp
            var date = new Date(timestamp);
    
            // Extract date components
            var day = ("0" + date.getUTCDate()).slice(-2);
            var month = ("0" + (date.getUTCMonth() + 1)).slice(-2);
            var year = date.getUTCFullYear();
    
            // Extract time components
            var hours = ("0" + date.getUTCHours()).slice(-2);
            var minutes = ("0" + date.getUTCMinutes()).slice(-2);
            var seconds = ("0" + date.getUTCSeconds()).slice(-2);
    
            // Combine into desired format
            var formattedDate = day + "-" + month + "-" + year;
            var formattedTime = hours + ":" + minutes + ":" + seconds;
    
            return formattedDate + " " + formattedTime;
        }
        function fundOdFormat(value, row, index) {
            if(row.type == 'deposit'){
                return value;
            }else{
                return "-"+value;
            }
        }
    </script>
    
    
    <script type="module">
      // Import the functions you need from the SDKs you need
      import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
      import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-analytics.js";
      // TODO: Add SDKs for Firebase products that you want to use
      // https://firebase.google.com/docs/web/setup#available-libraries
    
      // Your web app's Firebase configuration
      // For Firebase JS SDK v7.20.0 and later, measurementId is optional
      const firebaseConfig = {
        apiKey: "AIzaSyD-E6CADJYKx8zBrJ6ra19aeifMBdj3hu0",
        authDomain: "payrite-90caf.firebaseapp.com",
        projectId: "payrite-90caf",
        storageBucket: "payrite-90caf.appspot.com",
        messagingSenderId: "770005910489",
        appId: "1:770005910489:web:75cee7a934e21163f58fc8",
        measurementId: "G-YFM10RPTKQ"
      };
    
      // Initialize Firebase
      const app = initializeApp(firebaseConfig);
      const analytics = getAnalytics(app);
    </script>
    
    <script>
    function qrPaymentOtpSend() {
        let isValid = true;
        const errors = [];
    
        var amount = $("#qr_amount").val();
        var cust_name = $("#qr_cust_name").val();
        var cust_surname = $("#qr_cust_surname").val();
        var cust_mobile = $("#qr_cust_mobile").val();
        
        //Amount validation
        if (!amount) {
            isValid = false;
            errors.push("Amount is required");
            $("#qr_amount").addClass("error");
        } else if (isNaN(amount) || parseFloat(amount) <= 0) {
            isValid = false;
            errors.push("Please enter a valid positive amount");
            $("#qr_amount").addClass("error");
        }
    
        // Name validation
        if (!cust_name) {
            isValid = false;
            errors.push("Customer name is required");
            $("#qr_cust_name").addClass("error");
        } else if (!/^[a-zA-Z\s]{2,50}$/.test(cust_name)) {
            isValid = false;
            errors.push("Name should contain only letters and be 2-50 characters long");
            $("#qr_cust_name").addClass("error");
        }
    
        // Surname validation
        if (!cust_surname) {
            isValid = false;
            errors.push("Customer surname is required");
            $("#qr_cust_surname").addClass("error");
        } else if (!/^[a-zA-Z\s]{2,50}$/.test(cust_surname)) {
            isValid = false;
            errors.push("Surname should contain only letters and be 2-50 characters long");
            $("#qr_cust_surname").addClass("error");
        }
    
        // Mobile number validation
        if (!cust_mobile) {
            isValid = false;
            errors.push("Mobile number is required");
            $("#qr_cust_mobile").addClass("error");
        } else if (!/^[0-9]{10}$/.test(cust_mobile)) {
            isValid = false;
            errors.push("Please enter a valid 10-digit mobile number");
            $("#qr_cust_mobile").addClass("error");
        }
        
    
        // Display errors if any
        if (!isValid) {
            // Assuming you have a div with id "error-messages" to show errors
            $("#qr-error-messages").html(errors.map(error => `<div class="error-msg">${error}</div>`).join(''));
            return false;
        }
        
        
        $.ajax({
            url: '{{ route("qr_otp_send_retailer") }}',
            type: 'POST',
            data: {
                amount: amount,
                name: cust_name,
                surname: cust_surname,
                mobile: cust_mobile,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                console.log('OTP sent successfully:', response);
                // Handle success response here
                if(response.success === true) {
                    $("#qr_otp_section").removeClass("hide");
                    $("#qr_send_otp_btn").addClass("hide");
                    $("#qr_transaction_id").val(response.transaction_id);
                } else {
                    alert(response.message || 'Something went wrong');
                    // Handle failure case
                }
                
            },
            error: function(xhr, status, error) {
                console.error('Error sending OTP:', error);
                // Handle error here
            }
        });
        
        
    }
    
    function qrPaymentOtpVerify() {
        let isValid = true;
        const errors = [];
    
        var otp = $("#qr_cust_otp").val();
        var transaction_id = $("#qr_transaction_id").val();
        
        //validation
        if (!otp) {
            isValid = false;
            errors.push("Amount is required");
            
        } else if (!/^[0-9]{6}$/.test(otp)) {
            isValid = false;
            errors.push("Please enter a valid OTP");
            
        }
        
        if (!transaction_id) {
            isValid = false;
            errors.push("Transaction Not Valid.");
        }
        
        $.ajax({
            url: '{{ route("qr_otp_verify_retailer") }}',
            type: 'POST',
            data: {
                transaction_id: transaction_id,
                otp: otp,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                
                // Handle success response here
                if(response.success === true) {
                    $("#qr_otp_section").addClass("hide");
                    $("#qr_reg_section").addClass("hide");
                    $("#qr_img_section").removeClass("hide");
                    $("#qr_img").attr("src", response.qr);
                } else {
                    alert(response.message || 'Something went wrong');
                    // Handle failure case
                }
                
            },
            error: function(xhr, status, error) {
                console.error('Error sending OTP:', error);
                // Handle error here
            }
        });
    }
    </script>
    <!-- Loader -->
    <script>
        function loaderShow() {
                $("#spinner").hide();
                $("#pulse").show();
                
                // Show the loader with fade effect
                $("#loaderContainer").fadeIn(300);
                
                // Auto-hide after 5 seconds (for demo purposes)
                // setTimeout(function() {
                //     $("#loaderContainer").fadeOut(300);
                // }, 5000);
        }
        
        function loaderHide() {
            $("#loaderContainer").fadeOut(300);
        }
        $(document).ready(function() {
            // $("form").submit(function(e) {
            //     e.preventDefault(); // Prevent actual form submission
            //     $("#spinner").hide();
            //     $("#pulse").show();
            //     $("#loaderContainer").fadeIn(300);
            //     $(this).submit();
            // });
        });
    </script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
    @yield('page-script')
  </body>
</html>