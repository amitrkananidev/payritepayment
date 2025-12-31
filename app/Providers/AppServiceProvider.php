<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Auth;
use App\Models\kyc_docs;
use Log;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
    $currentDate = Carbon::now();
    app()->instance('currentDate', $currentDate);
    
    view()->composer('*', function ($view) {
        $data = [
            'pdfavailable' => false,
            'pdffile' => null,
            'pdfurl' => null
        ];
        
        // Check if user is authenticated
        if (Auth::check()) {
            $kyc_docs = kyc_docs::where('user_id', Auth::user()->id)->first();
            
            // Check if kyc_docs exists and has pan_number
            if ($kyc_docs && !empty($kyc_docs->pan_number)) {
                $pdfPath = 'uploads/tds/' . strtoupper($kyc_docs->pan_number) . '_Q4.pdf';
                $fullPath = public_path($pdfPath);
                $exists = file_exists($fullPath);
                log::info($fullPath);
                log::info($exists);
                $data = [
                    'pdfavailable' => $exists,
                    'pdffile' => $exists ? strtoupper($kyc_docs->pan_number) . '_Q4.pdf' : null,
                    'pdfurl' => $exists ? asset($pdfPath) : null
                ];
            }
        }
        
        $view->with($data);
    });
  }
}
