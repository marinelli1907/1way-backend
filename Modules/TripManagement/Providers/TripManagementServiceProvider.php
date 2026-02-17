<?php

namespace Modules\TripManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\TripManagement\Entities\ParcelRefund;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Observers\ParcelRefundObserver;
use Modules\TripManagement\Observers\TripRequestObserver;

// Interfaces
use Modules\TripManagement\Service\Interfaces\FareBiddingLogServiceInterface;
use Modules\TripManagement\Service\Interfaces\FareBiddingServiceInterface;
use Modules\TripManagement\Service\Interfaces\ParcelRefundProofServiceInterface;
use Modules\TripManagement\Service\Interfaces\ParcelRefundServiceInterface;
use Modules\TripManagement\Service\Interfaces\RecentAddressServiceInterface;
use Modules\TripManagement\Service\Interfaces\RejectedDriverRequestServiceInterface;
use Modules\TripManagement\Service\Interfaces\SafetyAlertServiceInterface;
use Modules\TripManagement\Service\Interfaces\TempTripNotificationServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRequestCoordinateServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRequestFeeServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRequestServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRequestTimeServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRouteServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripStatusServiceInterface;

// Implementations
use Modules\TripManagement\Service\FareBiddingLogService;
use Modules\TripManagement\Service\FareBiddingService;
use Modules\TripManagement\Service\ParcelRefundProofService;
use Modules\TripManagement\Service\ParcelRefundService;
use Modules\TripManagement\Service\RecentAddressService;
use Modules\TripManagement\Service\RejectedDriverRequestService;
use Modules\TripManagement\Service\SafetyAlertService;
use Modules\TripManagement\Service\TempTripNotificationService;
use Modules\TripManagement\Service\TripRequestCoordinateService;
use Modules\TripManagement\Service\TripRequestFeeService;
use Modules\TripManagement\Service\TripRequestService;
use Modules\TripManagement\Service\TripRequestTimeService;
use Modules\TripManagement\Service\TripRouteService;
use Modules\TripManagement\Service\TripStatusService;

class TripManagementServiceProvider extends ServiceProvider
{
    protected $moduleName = 'TripManagement';
    protected $moduleNameLower = 'tripmanagement';

    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        // Observers
        ParcelRefund::observe(ParcelRefundObserver::class);
        TripRequest::observe(TripRequestObserver::class);
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        // ✅ CRITICAL: bind interfaces → implementations
        $this->app->bind(TripRequestServiceInterface::class, TripRequestService::class);
        $this->app->bind(TripRequestFeeServiceInterface::class, TripRequestFeeService::class);
        $this->app->bind(TripRequestTimeServiceInterface::class, TripRequestTimeService::class);
        $this->app->bind(TripRequestCoordinateServiceInterface::class, TripRequestCoordinateService::class);
        $this->app->bind(TripRouteServiceInterface::class, TripRouteService::class);
        $this->app->bind(TripStatusServiceInterface::class, TripStatusService::class);

        $this->app->bind(FareBiddingServiceInterface::class, FareBiddingService::class);
        $this->app->bind(FareBiddingLogServiceInterface::class, FareBiddingLogService::class);

        $this->app->bind(ParcelRefundServiceInterface::class, ParcelRefundService::class);
        $this->app->bind(ParcelRefundProofServiceInterface::class, ParcelRefundProofService::class);

        $this->app->bind(RecentAddressServiceInterface::class, RecentAddressService::class);
        $this->app->bind(RejectedDriverRequestServiceInterface::class, RejectedDriverRequestService::class);
        $this->app->bind(TempTripNotificationServiceInterface::class, TempTripNotificationService::class);
        $this->app->bind(SafetyAlertServiceInterface::class, SafetyAlertService::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    public function registerViews()
    {
        $viewPath   = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
