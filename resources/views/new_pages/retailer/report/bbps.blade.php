@extends('new_layouts/app')

@section('title', 'DMT report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card bg-bbps-inner-page">
                    <img width="170" height="75" src="{{ asset('bbps/bbps_logo.png') }}" alt="bbps" style="position: absolute;
    right: 0;
    top: 0;"/>
                  <div class="card-body">
                    <h4 class="card-title">Transaction History</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" id="startDate" class="form-control" placeholder="Start Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="endDate" class="form-control" placeholder="End Date">
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="endDate" class="form-control" placeholder="Mobile Number">
                            </div>
                            <div class="col-md-12">
                                <hr>
                                <center>OR</center>
                                <hr>
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="endDate" class="form-control" placeholder="Transaction ID">
                            </div>
                            <div class="col-md-12">
                                <hr>
                            </div>
                            <div class="col-md-12">
                                <button type="button" id="filterBtn" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    
                  </div>
                </div>
              </div>
    </div>
</div>


@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/extensions/export/bootstrap-table-export.min.js"></script>

<script>
        
function priceFormatter(value, row) {
    var balanceFloat = parseFloat(value/100);
    return balanceFloat;
  }
function statuColunm(value, row, index) {
      if(value == 1){
        return [
          '<label class="badge badge-success">Success</label>'
        ].join('')
      }else if(value == 2 || value == 3){
          return [
          '<label class="badge btn-inverse-danger">Failed</label>'
        ].join('')
      }else if(value == 0 && row.eko_status == 3){
        if(row.api_id == 4){
            return [
          '<a href="https://user.payritepayment.in/retailer-services/dmt4/refund-otp/'+row.transaction_id+'" class="badge btn-inverse-info">refund Pending</a>'
        ].join('')
        }else{
            return [
          '<a href="https://user.payritepayment.in/retailer-services/bill-dmt/refund-otp/'+row.transaction_id+'" class="badge btn-inverse-info">refund Pending</a>'
        ].join('')
        }
        
      }else{
        return [
          '<label class="badge btn-inverse-warning">Pending</label>'
        ].join('')
      }
  }
  
  function kycDocuments(value, row, index) {
      return [
          '<button type="button" class="btn btn-primary" onClick="getKycDocument(&apos;'+value+'&apos;)">Receipt</button>'
        ].join('')
  }
  
  function getKycDocument(id) {
      window.open("https://user.payritepayment.in/retailer-receipt/dmt/"+id, "_blank");
  }
</script>
@endsection
