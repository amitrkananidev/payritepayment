<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
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
                <p>{{ $retailer->shopDetail->shop_name }}</p>
                <p>{{ $retailer->name }}</p>
                <p>{{ $retailer->mobile }}</p>
            </div>
        </div>
        <table class="details">
            <tr>
                <th>Customer Name</th>
                <td>{{ $decode->name }}</td>
                <th>Aadhar</th>
                <td>{{ $decode->aadhaar_number }}</td>
            </tr>
            <tr>
                <th>Bank Name</th>
                <td>{{ $bank->name }}</td>
                <th>RRN</th>
                <td>{{ $decode->rrn }}</td>
            </tr>
            <tr>
                <th>Transaction No.</th>
                <td>{{ $decode->CRN_U }}</td>
                <th>Transaction Date</th>
                <td>{{ $decode->created_at }}</td>
            </tr>
            
            
            <tr>
                <th>Transaction ID</th>
                <td>{{ $decode->transaction_id }}</td>
                <th>Payment Type</th>
                <td>Balance Enquiry</td>
            </tr>
        </table>
        <h3>Recent Transactions</h3>
        <table class="details">
            @foreach ($decode->mini_statement as $r)
            <tr>
                <th style="text-align: justify;">- {{ $r }}</th>
            </tr>
            @endforeach
            
        </table>
        <div class="footer">
            <!--<p>Customer charge is 1.2% including GST.</p>-->
            
        </div>
    </div>
</body>
</html>
