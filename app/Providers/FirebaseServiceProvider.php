<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('firebase', function () {
            $factory = (new Factory);

            $credentialsPath = storage_path(
                app('config')->get('services.firebase.credentials', '')
            );

            if ($credentialsPath && file_exists($credentialsPath)) {
                $factory = $factory->withServiceAccount($credentialsPath);
            }

            return $factory->create();
        });

        $this->app->singleton('firebase.messaging', function () {
            return app('firebase')->getMessaging();
        });
    }

    public function boot(): void
    {
        //
    }
}
