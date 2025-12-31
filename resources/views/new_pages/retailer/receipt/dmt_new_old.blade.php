<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
<style>
* {
    box-sizing: border-box;
}
  /* general styling */
body {
    font-family: "Open Sans", sans-serif;
}
  /* Create four equal columns that floats next to each other */
.column {
    float: left;
    width: 50%;
    padding: 10px;
    border-right: 1px dotted #000;
    height: 50%; /* Should be removed. Only for demonstration */
}

  /* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}
.d-flex{
    display: flex;
}
.flex-col{
    flex-direction: column;
}
.justify-content-between{
    justify-content: space-between;
}
.justify-content-center{
    justify-content: center;
}
.justify-content-end{
    justify-content: end;
}
.float-right{
    float: right;
}
.float-left{
    float: left;
}
.circle-logo{
    width: 60px;
}
.logo{
    width: 220px;
}
.title{
    margin-top: 5px;
}
.student-name{
    margin-bottom: 10px;
}
.bar-code{
    width: 200px;
    align-self: center;
    margin-top: 5px;
    margin-bottom: 10px;
}
.align-center{
    align-self: center;
}
/*table*/
table {
    margin-top: 10px;
    border: 1px solid #ccc;
    border-collapse: collapse;
    margin: 0;
    padding: 0;
    width: 100%;
    table-layout: fixed;
}
table tr {
    background-color: #fff;
    border: 1px solid #000;
    padding: .35em;
}
table th,
table td {
    padding: .625em;
    border: 1px solid #000;
}
/*table end*/
hr{
    border-top: 1px solid #000;
}

.centered-image-container {
    text-align: center;
    z-index: -1;
    position: absolute;
}

.centered-image {
    max-width: 30%;
    height: auto;
}
.wrap-text {
    word-wrap: break-word; /* Allows long words to be broken and wrap to the next line */
    white-space: normal; /* Ensures normal white space handling */
}
</style>
</head>
<body>
<div class="row">
    <div class="column">
        <div class="d-flex justify-content-between">
            <strong>Transaction ID # {{ $data->transaction_id }}</strong>
            <strong>Customer Copy</strong>
        </div>
        <div class="d-flex flex-col justify-content-center">
            <h4 class="align-center title"><img src="{{ asset('assets/images/Payrite_Logo.png') }}" style="width: 100%;"/></h4>
        </div>
        <div class="d-flex justify-content-between">
            <div class="d-flex flex-col">
                <span>Txn Date:</span>
                <?php $date = new DateTime($data->created_at); ?>
                <span>{{ $date->format('d.m.Y') }}</span>
            </div>
            <div class="d-flex flex-col">
                <span>Method:</span>
                
                <span>{{ $data->transfer_type }}</span>
            </div>
            <div class="d-flex flex-col">
                <span>Customer:</span>
                <span>{{ $data->customer_name }}<br>({{ $data->mobile }})</span>
            </div>
        </div>
        
        <table>
            <!--<thead>-->
            <!--    <tr>-->
            <!--    <th scope="col left" colspan="2">Description</th>-->
            <!--    <th scope="col right">Amount</th>-->
            <!--    </tr>-->
            <!--</thead>-->
            <tbody>
                <!--<tr>-->
                <!--<td scope="row" data-label="Account" colspan="2">Date & Time</td>-->
                <!--<td data-label="Amount">{{ $data->created_at }}</td>-->
                <!--</tr>-->
                <!--<tr>-->
                <!--<td scope="row" data-label="Account" colspan="2">Payment Method</td>-->
                <!--<td data-label="Amount">Cash</td>-->
                <!--</tr>-->
                <!--<tr>-->
                <!--<td scope="row" data-label="Account" colspan="2">Transfer Method</td>-->
                <!--<td data-label="Amount" class="wrap-text">{{ $data->transfer_type }}</td>-->
                <!--</tr>-->
                <tr>
                <td scope="row" data-label="Account" colspan="2">Account Holder</td>
                <td data-label="Amount" class="wrap-text">{{ $data->ben_name }}</td>
                </tr>
                <tr>
                <td scope="row" data-label="Account" colspan="2" >Account No.</td>
                <td data-label="Amount" class="wrap-text">{{ $data->ben_ac_number }}</td>
                </tr>
                <tr>
                <td scope="row" data-label="Account" colspan="2">IFSC</td>
                <td data-label="Amount" class="wrap-text">{{ $data->ben_ac_ifsc }}</td>
                </tr>
            </tbody>
        </table>
        @if($data->status == 1)
            <div class="centered-image-container">
                <img src="{{ asset('assets/images/success.png') }}" alt="Centered Image" class="centered-image">
            </div>
        @endif
        <hr/>
        <div class="d-flex justify-content-between">
            <strong>Fee</strong>
            <span>₹ {{ $data->fee }} </span>
        </div>
        <hr/>
        <div class="d-flex justify-content-between">
            <strong>Amount</strong>
            <span>₹ {{ $data->amount }} </span>
        </div>
        <hr/>
        <div class="d-flex justify-content-between">
            <strong>Total</strong>
            <span>₹ {{ $data->amount + $data->fee }} </span>
        </div>
        <br/>
        <hr/>

    </div>
</div>
<script>
    window.print();
</script>
</body>
</html>