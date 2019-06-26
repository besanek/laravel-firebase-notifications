<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications;

use Besanek\LaravelFirebaseNotifications\Exceptions\ConfigurationException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/firebase.php', 'firebase');

        $this->registerServiceAccount();
        $this->registerFirebase();
        $this->registerMessaging();
        $this->registerChannel();

        $this->extendNotifications();
    }

    private function registerServiceAccount(): void
    {
        $this->app->singleton(ServiceAccount::class, function (Application $application) {
            /** @var Repository $config */
            $config = $application->make(Repository::class);

            $credentials = $config->get('firebase.credentials');

            if (is_file($credentials)) {
                return ServiceAccount::fromJsonFile($credentials);
            }

            json_decode($credentials);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConfigurationException(
                    sprintf(
                        'Configuration firebase.credentials must be valid json file path or json. Value %s given.',
                        $credentials
                    )
                );
            }

            return ServiceAccount::fromJson($credentials);
        });
    }

    private function registerFirebase(): void
    {
        $this->app->singleton(Firebase::class, function (Application $application) {
            /** @var ServiceAccount $serviceAccount */
            $serviceAccount = $application->make(ServiceAccount::class);
            return (new Firebase\Factory)->withServiceAccount($serviceAccount)->create();
        });
    }

    private function registerMessaging(): void
    {
        $this->app->singleton(Firebase\Messaging::class, function (Application $application) {
            /** @var Firebase $firebase */
            $firebase = $application->make(Firebase::class);
            return $firebase->getMessaging();
        });
    }

    private function registerChannel(): void
    {
        $this->app->singleton(FirebaseChannel::class);
    }

    private function extendNotifications(): void
    {
        $this->app->extend(ChannelManager::class, function (ChannelManager $channelManager, Application $app) {
            $channelManager->extend('firebase', function () use ($app) {
                return $app->make(FirebaseChannel::class);
            });
            return $channelManager;
        });
    }
}
