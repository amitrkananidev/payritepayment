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
<?php $date = new DateTime($data->created_at); ?>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/images/Payrite_Logo.png') }}" alt="Relipay Logo">
            <div class="info">
                <p>{{ $retailer->shopDetail->shop_name }}</p>
                <p>{{ $retailer->name }}</p>
                <p>{{ $retailer->mobile }}</p>
            </div>
        </div>
        <table class="details">
            <tr>
                <th>Sender</th>
                <td>{{ $data->customer_name }}</td>
                <th>Mobile No.</th>
                <td>{{ $data_check->mobile }}</td>
            </tr>
            <tr>
                <th>Bene Name</th>
                <td>{{ $data_check->ben_name }}</td>
                <th>Bank Name</th>
                <td>{{ $data_check->bank_name }}</td>
            </tr>
            <tr>
                <th>Account Number</th>
                <td>{{ $data_check->ben_ac_number }}</td>
                <th>Transaction Date</th>
                <td>{{ $date->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <th>Payment Type</th>
                <td>{{ $data_check->transfer_type }}</td>
                <th>Amount</th>
                <td>₹ {{ $data->amount }}</td>
            </tr>
            <tr>
                <th>Fee</th>
                <td>₹ {{ $data->fee }}</td>
                <th>Total Amount</th>
                <td>₹ {{ $data->amount + $data->fee }}</td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td>{{ $data->transaction_id }}</td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <th>Status</th>
                @if($data->status == 1)<td class="success"> Success @elseif($data->status == 2)<td class="failed"> Failed @else<td class="pending"> Pending @endif</td>
                <th>Remarks</th>
                <td>-</td>
            </tr>
        </table>
        <div class="footer">
            <p>Customer charge is 1.2% including GST.</p>
            
        </div>
    </div>
</body>
</html>
