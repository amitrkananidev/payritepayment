<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fingerprint Device Initialization and Capture</title>
    
</head>
<body>
    <div class="overlay">
        <div id="loading"></div>
    </div>

    <!--<div id="loading"></div>-->

    <div class="pageBody" style="text-align:center;">
        <!--<h4>Test Mantra RD Service here</h4>-->
        <div style="padding:10px;">
            <!--<a href="https://download.mantratecapp.com/Forms/DownLoadFiles">Click here for download RD Service and MFS100 Driver Setup</a>-->
        </div>
        <div>
            <input type="button" class="btnAll" id="btnDiscoverAVDM" value="Start Test" onclick="discoverAvdm();" />
            <!--<input class="btnAll" onclick="deviceInfoAvdm();" type="button" value="Device Info">
            <input type="button" class="btnAll" onclick="CaptureAvdm();" value="Capture" />-->
        </div>
        <div id="divDvc" style="color:red;text-align:center;display:none;padding-top:10px;">
            <h3>Put your finger on device......</h3>
        </div>
        <div>
            <select id="ddlAVDM" class="form-control" style="width: 30%;margin-top:10px;display:none;">
                <option></option>
            </select>
        </div>
        <div id="divMsg"></div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script language="javascript" type="text/javascript">

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
            $("#divMsg").text("");

            var primaryUrl = "http://127.0.0.1:";
            var EndFor = 11120;

            var protocol = window.location.href;
            if (protocol.indexOf("https") >= 0) {
                primaryUrl = "https://127.0.0.1:";
                var EndFor = 8005;
            }
            else {

            }

            url = "";
            $("#ddlAVDM").empty();
            $("#divMsg").text("Please wait while discovering port 8005 and from 11100 to 11120.\nThis will take some time.");

            //for (var i = 11100; i <= 11120; i++) {
            for (var i = 8005; i <= EndFor; i++) {

                //$("#divMsg").text("Discovering RD service on port : " + i.toString());
                $(".overlay").show();
                $("#divMsg").text("");
                if (protocol.indexOf("https") >= 0) {
                    $("#divMsg").text("Please wait while discovering port 8005.\nThis will take some time.");
                }
                else {
                    $("#divMsg").text("Please wait while discovering port 8005 and from 11100 to 11120.\nThis will take some time.");
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
                            //$("#divMsg").text("");
                            $("#divMsg").append("<div style='color:red;'> - Discover RDService : <img style='width:25px;color:red;' src='false.png'/></div>");
                            $("#divMsg").append("<div style='color:red;'> - Error : Mantra RDService not discovered" + "</div>");
                            //return false;
                        }

                    },
                    error: function (jqXHR, ajaxOptions, thrownError) {

                        //alert(thrownError.toString().toLowerCase());
                        //$("#divMsg").append("<div style='color:red;'> - Discover RDService : <img style='width:25px;color:red;' src='false.png'/></div>");
                        // $("#divMsg").append("<div style='color:red;'> - Error : RDService not discovered. Please Check RDService installed or not.</div>");
                        $(".overlay").hide();
                        //var err = thrownError.toString();
                        $("#divMsg").append("<div style='color:red;'> - Discover RDService : <img style='width:25px;color:red;' src='false.png'/></div>");
                        $("#divMsg").append("<div style='color:red;'> - Error : RDService not discovered. Please Check RDService installed or not.</div>");
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

                    $("#divMsg").append("<div> - Discover RDService : <img style='width:25px' src='true.png'/></div>");
                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    $("#divMsg").append("<div style='color:red;'> - Discover RDService : <img style='width:25px;color:red;' src='false.png'/></div>");
                    $("#divMsg").append("<div style='color:red;'> - Error : " + thrownError + "</div>");

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
                        $("#divMsg").append("<div> - Device Info : <img style='width:25px' src='true.png'/></div>");

                        $("#divDvc").css("display", "block");
                        $(".overlay").show();

                        CaptureAvdm();

                    }
                    else {
                        $("#divMsg").append("<div style='color:red;'> - Device Info : <img style='width:25px;color:red;' src='false.png'/></div>");
                        $("#divMsg").append("<div style='color:red;'> - Error : Check device is connected or not.</div>");
                    }

                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    //alert(thrownError);

                    $("#divMsg").append("<div style='color:red;'> - Device Info : <img style='width:25px;color:red;' src='false.png'/></div>");
                    $("#divMsg").append("<div style='color:red;'> - Error : " + thrownError + "</div>");

                    // res = { httpStaus: httpStaus, err: getHttpError(jqXHR) };
                },
            });
            // return res;

        }
        var DemoFinalString = '';
        function CaptureAvdm() {

            var XML = ' <PidOptions ver="1.0"> <Opts fCount="1" fType="0" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P" /> ' + DemoFinalString + ' </PidOptions>';

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
                    alert(data);
                    console.log(data);
                    httpStaus = true;
                    res = { httpStaus: httpStaus, data: data };

                    var $doc = $.parseXML(data);
                    var Message = $($doc).find('Resp').attr('errInfo');

                    if (Message == "Success") {
                        $("#divMsg").append("<div> - Capture : <img style='width:25px' src='true.png'/></div>");
                        $(".overlay").hide();
                        $("#divDvc").css("display", "none");
                    }
                    else {
                        $("#divMsg").append("<div style='color:red;'> - Capture : <img style='width:25px;color:red;' src='false.png'/></div>");
                        $("#divMsg").append("<div style='color:red;'> - Error : " + Message + "</div>");
                        $(".overlay").hide();
                        $("#divDvc").css("display", "none");
                    }
                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    $(".overlay").hide();
                    $("#divDvc").css("display", "none");
                    $("#divMsg").append("<div style='color:red;'> - Capture : <img style='width:25px;color:red;' src='false.png'/></div>");
                    $("#divMsg").append("<div style='color:red;'> - Error : " + thrownError + "</div>");

                    res = { httpStaus: httpStaus, err: getHttpError(jqXHR) };
                },
            });

            return res;
        }
    </script>
</body>
</html>
