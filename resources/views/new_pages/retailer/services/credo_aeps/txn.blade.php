@extends('new_layouts/app')

@section('title', 'AEPS - 2FA')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card emboss bg-inner-page-dark">
                <div class="card-body">
                    <h4 class="card-title text-white">AEPS Transaction</h4>
                    <!--<p class="card-description"> Bordered layout </p>-->
                    <form class="forms-sample" action="{{ route('credo_aeps_transaction_retailer') }}" id="scandata" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="" id="PidData" name="PidData">
                        <input type="hidden" value="" id="mi" name="mi">
                        <input type="hidden" value="" id="rdsId" name="rdsId">
                        <input type="hidden" value="" id="rdsVer" name="rdsVer">
                        <input type="hidden" value="" id="srno" name="srno">
                        
                        <div class="form-group">
                            <label for="name" class="text-white">Transaction Type</label>
                            <select class="js-example-basic-single w-100 form-control" id="transaction_type" name="transaction_type" onchange="transactionType()" required="">
                                <option value="cash_withdrawal">Cash Withdrawal</option>
                                <option value="mini_statement">Mini Statment</option>
                                <option value="balance_enquiry">Balance Enquiry</option>
                            </select>
                        </div>
                         
                        <div class="form-group">
                            <label for="name" class="text-white">Aadhar Number</label>
                            <input type="text" class="form-control" name="aadhar" id="aadhar" placeholder="Aadhar Number" value="" required="">
                        </div>
                        <div class="form-group">
                            <label for="name" class="text-white">Bank</label>
                            <select class="js-example-basic-single w-100 form-control" id="Bank" name="bank_name" required="">
                                @foreach($banks as $r)
                                <option value="{{ $r->credopay_id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name" class="text-white">Amount</label>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="" required="">
                        </div>
                                  
                        <div class="button-container">
                            <center>
                                <button class="btn btn-primary" type="button" style="background: #edf7ef !important;border: none;color: black;" id="btnDiscoverAVDM" value="Start Test" onclick="discoverAvdm();">
                                    <!--<dotlottie-player src="https://lottie.host/92f5f83d-80e5-4701-bdc1-9cf8f16f5919/jGL2G2Pgxk.json" background="transparent" speed="1" style="width: 50px; height: 50px;" loop autoplay></dotlottie-player>-->
                                    <img width="50" height="50" src="https://img.icons8.com/dotty/100/fingerprint-scan--v1.png" alt="fingerprint-scan--v1"/>
                                    <br>MANTRA
                                </button>
                                <button class="btn btn-primary" type="button" style="background: #edf7ef !important;border: none;color: black;" id="btnDiscoverAVDM" value="Start Test" onclick="Capture();">
                                    <!--<dotlottie-player src="https://lottie.host/92f5f83d-80e5-4701-bdc1-9cf8f16f5919/jGL2G2Pgxk.json" background="transparent" speed="1" style="width: 50px; height: 50px;" loop autoplay></dotlottie-player>-->
                                    <img width="50" height="50" src="https://img.icons8.com/dotty/100/fingerprint-scan--v1.png" alt="fingerprint-scan--v1"/>
                                    <br>MORPHO
                                </button>
                                <button class="btn btn-primary" type="button" style="background: #edf7ef !important;border: none;color: black;" id="btnDiscoverAVDM" value="Start Test" onclick="CaptureStar();">
                                    <!--<dotlottie-player src="https://lottie.host/92f5f83d-80e5-4701-bdc1-9cf8f16f5919/jGL2G2Pgxk.json" background="transparent" speed="1" style="width: 50px; height: 50px;" loop autoplay></dotlottie-player>-->
                                    <img width="50" height="50" src="https://img.icons8.com/dotty/100/fingerprint-scan--v1.png" alt="fingerprint-scan--v1"/>
                                    <br>STARTECH
                                </button>
                            </center>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card bg-inner-page">
                  <div class="card-body">
                    
                    
                    
                    
                </div>
            </div>
            
        </div>
    </div>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>
    <div class="overlay">
        <div id="loading"></div>
    </div>

    <div id="loading"></div>

    <div class="pageBody" style="text-align:center;">
        <!--<h4>Test Mantra RD Service here</h4>-->
        <!--<div style="padding:10px;">-->
        <!--    <a href="https://download.mantratecapp.com/Forms/DownLoadFiles">Click here for download RD Service and MFS100 Driver Setup</a>-->
        <!--</div>-->
        <div>
            <!--<input type="button" class="btnAll" id="btnDiscoverAVDM" value="Start Test" onclick="discoverAvdm();" />-->
            <!--<input class="btnAll" onclick="deviceInfoAvdm();" type="button" value="Device Info">-->
            <!--<input type="button" class="btnAll" onclick="CaptureAvdm();" value="Capture" />-->
        </div>
        
        <div>
            <select id="ddlAVDM" class="form-control" style="width: 30%;margin-top:10px;display:none;">
                <option></option>
            </select>
        </div>
        

    </div>
    
</body>
</html>
@endsection

@section('page-script')
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script language="javascript" type="text/javascript">
        
            function transactionType() {
                
                if ($("#transaction_type").val() === 'cash_withdrawal') {
                    $('#amount').prop('readonly', false);
                    $('#amount').val('');
                } else {
                    $('#amount').prop('readonly', true);
                    $('#amount').val(0);
                }
            }
        $(window).ready(function () {

            //var protocol = window.location.href;

            //alert(protocol);
            //if (protocol.indexOf("https") >= 0) {
            //    alert("ok");
            //}
            //else {
            //    alert("no");
            //}
            //if (protocol.startsWith("https")) {
            //    primaryUrl = "https://127.0.0.1:";
            //    alert("https");
            //}
            //else {
            //    alert("http");
            //}

        });
        var dinfo = "";

        function discoverAvdm() {

            var dis = 0;

            $("#divPidInfo").text("");

            var primaryUrl = "http://127.0.0.1:";
            var EndFor = 11120;

            var protocol = window.location.href;
            if (protocol.indexOf("https") >= 0) {
                primaryUrl = "https://127.0.0.1:";
                var EndFor = 11120;
            }
            else {

            }

            url = "";
            $("#ddlAVDM").empty();

            //for (var i = 11100; i <= 11120; i++) {
            for (var i = 8005; i <= EndFor; i++) {

                $(".overlay").show();
                if (protocol.indexOf("https") >= 0) {
                }
                else {
                }

                var verb = "RDSERVICE";
                var err = "";

                var res;
                $.support.cors = true;
                var httpStaus = false;
                var jsonstr = "";
                var data = new Object();
                var obj = new Object();
                $.ajax({

                    type: "RDSERVICE",
                    async: false,
                    crossDomain: true,
                    url: primaryUrl + i.toString(),
                    contentType: "text/xml; charset=utf-8",
                    processData: false,
                    cache: false,
                    async: false,
                    crossDomain: true,

                    success: function (data) {

                        httpStaus = true;
                        res = { httpStaus: httpStaus, data: data };
                        //alert(data);
                        finalUrl = primaryUrl + i.toString();
                        var $doc = $.parseXML(data);
                        var CmbData1 = $($doc).find('RDService').attr('status');
                        var CmbData2 = $($doc).find('RDService').attr('info');
                        //debugger;
                        $("#ddlAVDM").append('<option value=' + i.toString() + '>(' + CmbData1 + ')' + CmbData2 + '</option>')

                        if (CmbData1 == "READY") {
                            dinfo = "1";
                        }
                        else {
                            dinfo = "0";
                        }

                        if (CmbData2.indexOf("Mantra") >= 0) {
                            //if (CmbData2 == "Mantra Authentication Vendor Device Manager") {
                            dis = 1;
                            $("select#ddlAVDM").prop('selectedIndex', 0);

                            var PortVal = $('#ddlAVDM').val($('#ddlAVDM').find('option').first().val()).val();

                            if (PortVal > 11099) {
                                discoverAvdmFirstNode(PortVal);
                            }
                            if (PortVal == 8005) {
                                discoverAvdmFirstNode(PortVal);
                            }
                        }
                        else {
                            
                            //return false;
                        }

                    },
                    error: function (jqXHR, ajaxOptions, thrownError) {

                        //alert(thrownError.toString().toLowerCase());
                        $(".overlay").hide();
                        //var err = thrownError.toString();
                    },
                });
                $("#ddlAVDM").val("0");

                if (dis == 1) {
                    break
                }
                if (i == 8005) {
                    //i = 11100;
                    i = 11099;
                }
                $(".overlay").hide();
            }
        }

        function discoverAvdmFirstNode(PortNo) {

            var primaryUrl = "http://127.0.0.1:";

            try {
                var protocol = window.location.href;
                if (protocol.indexOf("https") >= 0) {
                    primaryUrl = "https://127.0.0.1:";
                }
            } catch (e)
            { }


            url = "";
            var verb = "RDSERVICE";
            var err = "";
            var res;
            $.support.cors = true;
            var httpStaus = false;
            var jsonstr = "";
            var data = new Object();
            var obj = new Object();

            $.ajax({
                type: "RDSERVICE",
                async: false,
                crossDomain: true,
                url: primaryUrl + PortNo,
                contentType: "text/xml; charset=utf-8",
                processData: false,
                cache: false,
                async: false,
                crossDomain: true,
                success: function (data) {
                    httpStaus = true;
                    res = { httpStaus: httpStaus, data: data };

                    var $doc = $.parseXML(data);

                    MethodInfo = $($doc).find('Interface').eq(0).attr('path');
                    MethodCapture = $($doc).find('Interface').eq(1).attr('path');
                },
                error: function (jqXHR, ajaxOptions, thrownError) {

                    res = { httpStaus: httpStaus, err: getHttpError(jqXHR) };
                    // return false;

                },
            });
            $(".overlay").hide();
            //return res;
            deviceInfoAvdm();
        }

        function deviceInfoAvdm() {
            url = "";

            finalUrl = "http://127.0.0.1:" + $("#ddlAVDM").val();

            try {
                var protocol = window.location.href;
                if (protocol.indexOf("https") >= 0) {
                    finalUrl = "https://127.0.0.1:" + $("#ddlAVDM").val();
                }
            } catch (e)
            { }


            var verb = "DEVICEINFO";
            //alert(finalUrl);

            var err = "";

            var res;
            $.support.cors = true;
            var httpStaus = false;
            var jsonstr = "";
            $.ajax({

                type: "DEVICEINFO",
                async: false,
                crossDomain: true,
                url: finalUrl + MethodInfo,
                contentType: "text/xml; charset=utf-8",
                processData: false,
                success: function (data) {
                    //alert(data);
                    httpStaus = true;
                    res = { httpStaus: httpStaus, data: data };

                    //$('#divDeviceInfo').text(data);

                    if (dinfo == "1") {

                        
                        $(".overlay").show();

                        CaptureAvdm();

                    }
                    else {
                    }

                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    //alert(thrownError);

                    // res = { httpStaus: httpStaus, err: getHttpError(jqXHR) };
                },
            });
            // return res;

        }
        var DemoFinalString = '';
        function CaptureAvdm() {

            var XML = ' <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" pgCount="2" format="0"   pidVer="2.0" timeout="10000" pTimeout="20000" posh="UNKNOWN" env="P" /> </PidOptions>';

            var verb = "CAPTURE";
            var err = "";

            var res;
            $.support.cors = true;
            var httpStaus = false;
            var jsonstr = "";
            $.ajax({

                type: "CAPTURE",
                async: false,
                crossDomain: true,
                url: finalUrl + MethodCapture,
                data: XML,
                contentType: "text/xml; charset=utf-8",
                processData: false,
                success: function (data) {
                    console.log(data);
                    httpStaus = true;
                    res = { httpStaus: httpStaus, data: data };

                    var $doc = $.parseXML(data);
                    var Message = $($doc).find('Resp').attr('errInfo');
                    
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(data, "text/xml");
                    const srno = xmlDoc.querySelector('Param[name="srno"]').getAttribute('value');
                    const rdsVer = xmlDoc.querySelector('DeviceInfo').getAttribute('rdsVer');
                    const deviceInfo = xmlDoc.querySelector('DeviceInfo');
                    const rdsId = deviceInfo.getAttribute('rdsId');
                    const mi = deviceInfo.getAttribute('mi');
                    
                    $("#PidData").val(data);
                    $("#rdsId").val(rdsId);
                    $("#mi").val(mi);
                    $("#rdsVer").val(rdsVer);
                    $("#srno").val(srno);
                    $("#scandata").submit();
                    if (Message == "Success") {
                        // $(".overlay").hide();
                        
                        
                    }
                    else {
                        $(".overlay").hide();
                        
                    }
                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    $(".overlay").hide();

                    res = { httpStaus: httpStaus, err: getHttpError(jqXHR) };
                },
            });

            return res;
        }
        
        function Capture()
        {
        
          var url = "http://127.0.0.1:11101/capture";
        
           var PIDOPTS=' <PidOptions ver="1.0"> <Opts fCount="1" fType="2" pCount="0" iCount="0" pgCount="2" format="0" pidVer="2.0" timeout="10000" pTimeout="20000" posh="UNKNOWN" env="P"/> </PidOptions>';
           var XML = ' <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" pgCount="2" format="0"   pidVer="2.0" timeout="10000" pTimeout="20000" posh="UNKNOWN" env="P" /> </PidOptions>';
           /*
           format=\"0\"     --> XML
           format=\"1\"     --> Protobuf
           */
         var xhr;
        			var ua = window.navigator.userAgent;
        			var msie = ua.indexOf("MSIE ");
        
        			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
        			{
        				//IE browser
        				xhr = new ActiveXObject("Microsoft.XMLHTTP");
        			} else {
        				//other browser
        				xhr = new XMLHttpRequest();
        			}
                
                xhr.open('CAPTURE', url, true);
        		xhr.setRequestHeader("Content-Type","text/xml");
        		xhr.setRequestHeader("Accept","text/xml");
        
                xhr.onreadystatechange = function () {
        		//if(xhr.readyState == 1 && count == 0){
        		//	fakeCall();
        		//}
        if (xhr.readyState == 4){
                    var status = xhr.status;
                    //parser = new DOMParser();
                    if (status == 200) {
                    var test1=xhr.responseText;
                    var test2=test1.search("errCode");
        			var test6=getPosition(test1, '"', 2);
        			var test4=test2+9;
        			var test5=test1.slice(test4, test6);
        			if (test5>0)
        			{
                    // alert(xhr.responseText);
        			//document.getElementById('text').value = xhr.responseText;
        			}
        			else
        			{
            // 			alert("Captured Successfully");
            			console.log(xhr.response);
            			data = xhr.response;
            			
            			var $doc = $.parseXML(data);
                        var Message = $($doc).find('Resp').attr('errInfo');
                        
                        
                        const parser = new DOMParser();
                        const xmlDoc = parser.parseFromString(data, "text/xml");
                        
                        
                        const srno = xmlDoc.querySelector('Param[name="srno"]').getAttribute('value');
                        const rdsVer = xmlDoc.querySelector('DeviceInfo').getAttribute('rdsVer');
                        const deviceInfo = xmlDoc.querySelector('DeviceInfo');
                        const rdsId = deviceInfo.getAttribute('rdsId');
                        const mi = deviceInfo.getAttribute('mi');
                        
                        $("#PidData").val('<'+'?xml version="1.0" ?>' + data);
                        $("#rdsId").val(rdsId);
                        $("#mi").val(mi);
                        $("#rdsVer").val(rdsVer);
                        $("#srno").val(srno);
                        $("#scandata").submit();
        			//document.getElementById('text').value = "Captured Successfully";
        			}
        
        
                    } else 
                    {
                        
        	            console.log(xhr.response);
        
                    }
        			}
        
                };
        
                xhr.send(PIDOPTS);
        	
        }
        
        function getPosition(string, subString, index) {
          return string.split(subString, index).join(subString).length;
        }
        
        function CaptureStar()
        {
        
          var url = "http://localhost:11100/rd/capture";
        
           var PIDOPTS=' <PidOptions ver="1.0"> <Opts fCount="1" fType="2" pCount="0" iCount="0" pgCount="2" format="0" pidVer="2.0" timeout="10000" pTimeout="20000" posh="UNKNOWN"/> </PidOptions>';
           var XML = ' <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" pgCount="2" format="0"   pidVer="2.0" timeout="10000" pTimeout="20000" posh="UNKNOWN" env="P" /> </PidOptions>';
           /*
           format=\"0\"     --> XML
           format=\"1\"     --> Protobuf
           */
         var xhr;
        			var ua = window.navigator.userAgent;
        			var msie = ua.indexOf("MSIE ");
        
        			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
        			{
        				//IE browser
        				xhr = new ActiveXObject("Microsoft.XMLHTTP");
        			} else {
        				//other browser
        				xhr = new XMLHttpRequest();
        			}
                
                xhr.open('CAPTURE', url, true);
        		xhr.setRequestHeader("Content-Type","text/xml");
        		xhr.setRequestHeader("Accept","text/xml");
        
                xhr.onreadystatechange = function () {
        		//if(xhr.readyState == 1 && count == 0){
        		//	fakeCall();
        		//}
        if (xhr.readyState == 4){
                    var status = xhr.status;
                    //parser = new DOMParser();
                    if (status == 200) {
                    var test1=xhr.responseText;
                    var test2=test1.search("errCode");
        			var test6=getPosition(test1, '"', 2);
        			var test4=test2+9;
        			var test5=test1.slice(test4, test6);
        			if (test5>0)
        			{
                    
        			//document.getElementById('text').value = xhr.responseText;
        			}
        			else
        			{
            // 			alert("Captured Successfully");
            			console.log(xhr.response);
            			data = xhr.response;
            			
            			var $doc = $.parseXML(data);
                        var Message = $($doc).find('Resp').attr('errInfo');
                        
                        
                        const parser = new DOMParser();
                        const xmlDoc = parser.parseFromString(data, "text/xml");
                        
                        
                        const srno = xmlDoc.querySelector('Param[name="srno"]').getAttribute('value');
                        const rdsVer = xmlDoc.querySelector('DeviceInfo').getAttribute('rdsVer');
                        const deviceInfo = xmlDoc.querySelector('DeviceInfo');
                        const rdsId = deviceInfo.getAttribute('rdsId');
                        const mi = deviceInfo.getAttribute('mi');
                        
                        $("#PidData").val('<'+'?xml version="1.0" ?>' + data);
                        $("#rdsId").val(rdsId);
                        $("#mi").val(mi);
                        $("#rdsVer").val(rdsVer);
                        $("#srno").val(srno);
                        $("#scandata").submit();
        			//document.getElementById('text').value = "Captured Successfully";
        			}
        
        
                    } else 
                    {
                        
        	            console.log(xhr.response);
        
                    }
        			}
        
                };
        
                xhr.send(PIDOPTS);
        	
        }
        
        
    </script>
@endsection
