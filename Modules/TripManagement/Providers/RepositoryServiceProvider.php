<?php

namespace Modules\TripManagement\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        /**
         * ----------------------------
         * REPOSITORIES
         * ----------------------------
         * Expected:
         *  - Concrete: Modules/TripManagement/Repository/Eloquent/FooRepository.php
         *  - Interface: Modules/TripManagement/Repository/FooRepositoryInterface.php
         *  - Namespace interface: Modules\TripManagement\Repository\FooRepositoryInterface
         *  - Namespace concrete:  Modules\TripManagement\Repository\Eloquent\FooRepository
         */
        $repositoriesPath = base_path('Modules/TripManagement/Repository/Eloquent');
        $repositoryInterfacePath = base_path('Modules/TripManagement/Repository');

        if (File::isDirectory($repositoriesPath)) {
            $repositoryFiles = File::files($repositoriesPath);

            foreach ($repositoryFiles as $file) {
                // Skip non-php (paranoia guard)
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filename = File::name($file->getRealPath()); // e.g. TripRequestRepository
                $interfaceName = $filename . 'Interface';     // e.g. TripRequestRepositoryInterface
                $interfacePath = $repositoryInterfacePath . DIRECTORY_SEPARATOR . $interfaceName . '.php';

                if (File::exists($interfacePath)) {
                    $interface  = 'Modules\\TripManagement\\Repository\\' . $interfaceName;
                    $repository = 'Modules\\TripManagement\\Repository\\Eloquent\\' . $filename;

                    $this->app->bind($interface, $repository);
                }
            }
        }

        /**
         * ----------------------------
         * SERVICES
         * ----------------------------
         * Your production error shows:
         *  Modules\TripManagement\Service\Interfaces\TripRequestServiceInterface
         *
         * So interfaces folder must be:
         *  Modules/TripManagement/Service/Interfaces
         *
         * We'll support BOTH:
         *  - Service/Interfaces (correct)
         *  - Service/Interface (legacy typo)
         */
        $servicesPath = base_path('Modules/TripManagement/Service');

        $serviceInterfacePathPlural   = base_path('Modules/TripManagement/Service/Interfaces');
        $serviceInterfacePathSingular = base_path('Modules/TripManagement/Service/Interface');

        $serviceInterfacePath = File::isDirectory($serviceInterfacePathPlural)
            ? $serviceInterfacePathPlural
            : $serviceInterfacePathSingular;

        $serviceInterfaceNamespace = File::isDirectory($serviceInterfacePathPlural)
            ? 'Modules\\TripManagement\\Service\\Interfaces\\'
            : 'Modules\\TripManagement\\Service\\Interface\\';

        if (File::isDirectory($servicesPath)) {
            $serviceFiles = File::files($servicesPath);

            foreach ($serviceFiles as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filename = File::name($file->getRealPath()); // e.g. TripRequestService

                // Don't accidentally bind base classes/helpers if they exist
                // (optional: comment out if you want everything auto-bound)
                if (str_contains($filename, 'Base') || str_contains($filename, 'Abstract')) {
                    continue;
                }

                $interfaceName = $filename . 'Interface'; // e.g. TripRequestServiceInterface
                $interfacePath = $serviceInterfacePath . DIRECTORY_SEPARATOR . $interfaceName . '.php';

                if (File::exists($interfacePath)) {
                    $serviceInterface = $serviceInterfaceNamespace . $interfaceName;
                    $service          = 'Modules\\TripManagement\\Service\\' . $filename;

                    $this->app->bind($serviceInterface, $service);
                }
            }
        }
    }

    public function provides()
    {
        return [];
    }
}
