@extends('new_layouts/app')

@section('title', 'Business report')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.6/dist/bootstrap-table.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Business report</h4>
                    <!--<p class="card-description"> Add class <code>.table-hover</code>-->
                    </p>
                    <form class="forms-sample" action="{{ route('business_report_export') }}" method="post">
                        @csrf
                    <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" id="startDate" class="form-control" name="start_date" placeholder="Start Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="endDate" class="form-control" name="end_date" placeholder="End Date">
                            </div>
                            <div class="col-md-3">
                                <select class="js-example-basic-single w-100" id="user_ids" name="user_ids" required="">
                                   @foreach($users as $r)
                                   <option value="{{ $r->id }}">{{ $r->name }} {{ $r->surname }} ({{ $r->mobile }})</option>
                                   @endforeach
                                </select>
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

