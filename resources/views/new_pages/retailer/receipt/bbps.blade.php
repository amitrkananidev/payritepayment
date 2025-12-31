<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 1000px; /* Half of A4 width in pixels */
            border: 1px solid #ccc;
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 100px;
        }
        .header .info {
            text-align: right;
        }
        .info p {
            margin: 0;
        }
        .details {
            width: 100%;
            border-collapse: collapse;
        }
        .details td, .details th {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .success {
            color: green;
        }
        .failed {
            color: red;
        }
        .pending {
            color: orange;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/images/Payrite_Logo.png') }}" alt="Relipay Logo">
            
            <div class="info">
                <p>Demo Retailer Shop</p>
                <p>Jone Deo</p>
                <p>9638527410</p>
            </div>
            <img src="{{ asset('bbps/assured.png') }}" alt="Relipay Logo">
        </div>
        <table class="details">
            <tr>
                <th>Name Of Biller</th>
                <td>DTH</td>
                <th>Mobile No.</th>
                <td>9876543210</td>
            </tr>
            <tr>
                <th>Bill Number</th>
                <td>5434735438432435</td>
                <th>Bill Date</th>
                <td>01-08-2025</td>
            </tr>
            <tr>
                <th>Bill Due Date</th>
                <td>02-08-2025</td>
                <th>B-Connect Transaction ID</th>
                <td>WB0K9HI5LDWPVEJSUCJ64UPHQDS43091152</td>
            </tr>
            <tr>
                <th>Registered Mobile Number</th>
                <td>9876543210</td>
                <th>Bill Amount</th>
                <td>₹ 100</td>
            </tr>
            <tr>
                <th>Customer Convenience Fee</th>
                <td>₹ 0.0</td>
                <th>Total Amount</th>
                <td>₹ 100</td>
            </tr>
            <tr>
                <th>Transaction Date</th>
                <td>01-08-2025 10:12:15</td>
                <th>Transaction ID</th>
                <td>SATBBP240427453956</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>Success</td>
                <th>Remarks</th>
                <td>-</td>
            </tr>
        </table>
        <div class="footer">
            <!--<p>Customer charge is 1.2% including GST.</p>-->
            
        </div>
    </div>
</body>
</html>
