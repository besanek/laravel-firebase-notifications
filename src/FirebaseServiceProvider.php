<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerChannel();

        $this->extendNotifications();
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
