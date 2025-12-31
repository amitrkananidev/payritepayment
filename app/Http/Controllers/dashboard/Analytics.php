<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
// use Excel;
use Mail;
use Auth;
use DB;
use Hash;

class Analytics extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('content.dashboard.dashboards-analytics');
    }
}
