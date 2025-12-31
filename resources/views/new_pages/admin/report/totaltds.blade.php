@extends('new_layouts/app')

@section('title', 'Total TDS report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Total TDS report</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <form class="forms-sample" action="{{ route('total_tds_data_export') }}" method="post">
                        @csrf
                    <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" id="startDate" class="form-control" name="start_date" placeholder="Start Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="endDate" class="form-control" name="end_date" placeholder="End Date">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" id="filterBtn" class="btn btn-primary">Export CSV</button>
                            </div>
                            
                        </div>
                    </form>
                  </div>
                </div>
              </div>
    </div>
</div>


@endsection

