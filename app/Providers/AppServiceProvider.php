<?php

namespace App\Providers;

use App\Models\Kategori;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
        
        View::composer('layouts.global', function ($view) {
            $kategori = Kategori::all();
            $view->with([
                'kategori' => $kategori,
            ]);
        });
    }
}
