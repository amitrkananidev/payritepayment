@extends('new_layouts/app')

@section('title', 'AEPS - Mini Statment')

@section('page-style')

@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-6 col-lg-6 grid-margin stretch-card">
                              <div class="card emboss bg-mini-statment-section">
                                <div class="card-body">
                                  <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="card-title card-title-dash">MINI STATMENT</h4>
                                    <p class="mb-0">{{ $decode->name }}</p>
                                  </div>
                                  <ul class="bullet-line-list">
                                    @foreach ($decode->mini_statement as $r)
                                    <li>
                                      <div class="d-flex justify-content-between">
                                        <div><span class="text-light-green">{{ $r }}</div>
                                        
                                      </div>
                                    </li>
                                    @endforeach
                                    
                                  </ul>
                                  <div class="list align-items-center pt-3">
                                    <div class="wrapper w-100">
                                      <p class="mb-0">
                                        
                                      </p>
                                    </div>
                                  </div>
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

@endsection

@section('page-script')
@endsection
