<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests;

use Besanek\LaravelFirebaseNotifications\FirebaseServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [FirebaseServiceProvider::class];
    }
}
