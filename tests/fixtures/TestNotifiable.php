<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests\Fixtures;

use Illuminate\Notifications\Notifiable;

class TestNotifiable {

    use Notifiable;

    /** @var mixed */
    private $targets;

    /**
     * @param $targets
     */
    public function __construct($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @return mixed
     */
    public function routeNotificationForFirebase()
    {
        return $this->targets;
    }
}