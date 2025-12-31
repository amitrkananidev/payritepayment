@extends('new_layouts/app')

@section('title', 'Dashboard')

@section('page-style')

@endsection

@section('content')
<?php 
date_default_timezone_set('Asia/Kolkata');
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>airpay</title>
<script type="text/javascript">
function submitForm(){
			var form = document.forms[0];
			form.submit();
		}
</script>
</head>
<body onload="javascript:submitForm()">
<center>
<table width="500px;">
	<tr>
		<td align="center" valign="middle">&nbsp;</td>
	</tr>
	<tr>
		<td align="center" valign="middle">
			<form action="https://payments.airpay.co.in/pay/index.php" id="pgform" method="post" runat="server" target="airpayiframe">	
			@csrf
			    <input type="hidden" name="buyerEmail" value="{{ $buyerEmail }}">
			    <input type="hidden" name="amount" value="{{ $amount }}">
			    <input type="hidden" name="buyerPhone" value="{{ $buyerPhone }}">
                <input type="hidden" name="privatekey" value="<?php echo $privatekey; ?>">
                <input type="hidden" name="mercid" value="<?php echo $mercid; ?>">
				<input type="hidden" name="orderid" value="<?php echo $orderid; ?>">
				<input type="hidden" name="kittype" value="iframe">
 		        <input type="hidden" name="currency" value="356">
		        <input type="hidden" name="isocurrency" value="INR">
				<input type="hidden" name="chmod" value="{{ $method }}">
				<input type="hidden" name="buyerFirstName" value="{{ $buyerFirstName }}">
				<input type="hidden" name="buyerLastName" value="{{ $buyerLastName }}">
				<input type="hidden" name="buyerAddress" value="{{ trim(Auth::user()->addresses->cities->name) }}">
				<input type="hidden" name="buyerCity" value="{{ trim(Auth::user()->addresses->cities->name) }}">
				<input type="hidden" name="buyerState" value="{{ trim(Auth::user()->addresses->cities->state_name) }}">
				<input type="hidden" name="buyerPinCode" value="{{ trim(Auth::user()->addresses->pincode) }}">
				<input type="hidden" name="buyerCountry" value="India">
				<input type="hidden" name="checksum" value="<?php echo $checksum; ?>">
				

			</form>
		</td>

	</tr>

</table>

<iframe name="airpayiframe" class="kitiframe"></iframe>
</center>
<style>
    iframe.kitiframe {  width: 100%;  height: 600px; } 
</style>
</body>
</html>
@endsection

@section('page-script')
@endsection