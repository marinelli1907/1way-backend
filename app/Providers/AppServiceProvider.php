<?php

namespace App\Providers;

use App\Lib\Units;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->environment('live')) {
            // URL::forceScheme('https');
        }
        Paginator::useBootstrap();

        // ── 1Way Units — Blade helpers ──────────────────────────────────
        // Usage in Blade:  @distance(42.5)  @speed(100)  @currency(19.99)
        Blade::directive('distance',    fn($e) => "<?php echo \\App\\Lib\\Units::distance($e); ?>");
        Blade::directive('speed',       fn($e) => "<?php echo \\App\\Lib\\Units::speed($e); ?>");
        Blade::directive('weight',      fn($e) => "<?php echo \\App\\Lib\\Units::weight($e); ?>");
        Blade::directive('temperature', fn($e) => "<?php echo \\App\\Lib\\Units::temperature($e); ?>");
        Blade::directive('money',       fn($e) => "<?php echo \\App\\Lib\\Units::currency($e); ?>");
        Blade::directive('distLabel',   fn()   => "<?php echo \\App\\Lib\\Units::distanceLabel(); ?>");
        Blade::directive('speedLabel',  fn()   => "<?php echo \\App\\Lib\\Units::speedLabel(); ?>");
        Blade::directive('weightLabel', fn()   => "<?php echo \\App\\Lib\\Units::weightLabel(); ?>");
        Blade::directive('currency',    fn()   => "<?php echo \\App\\Lib\\Units::currencyCode(); ?>");
    }
}
