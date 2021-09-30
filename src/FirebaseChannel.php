<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications;

use Besanek\LaravelFirebaseNotifications\Exceptions\ChannelException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\MulticastSendReport;

class FirebaseChannel
{
    /**
     * @var Messaging
     */
    private $messaging;

    /**
     * @param Messaging $messaging
     */
    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * @param Notifiable $notifiable
     * @param Notification $notification
     * @return MulticastSendReport
     */
    public function send($notifiable, Notification $notification): MulticastSendReport
    {
        if (!method_exists($notification, 'toFirebase')) {
            throw new ChannelException('Method toFirebase() is missing');
        }

        $message = $notification->toFirebase();

        if (!$message instanceof Messaging\Message) {
            throw new ChannelException(
                'Channel expected return type %s, %s is returned', Messaging\Message::class, gettype($message)
            );
        }

        $targets = $notifiable->routeNotificationFor('firebase', $notification);
        $targets = !$targets ? [] : Arr::wrap($targets);
        $this->validateTargets($targets);

        return $this->sendToTargets($message, $targets);
    }

    /**
     * @param Messaging\Message $message
     * @param array $targets
     * @return MulticastSendReport
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    protected function sendToTargets(Messaging\Message $message, array $targets): MulticastSendReport
    {
        if (empty($targets)) {
            return MulticastSendReport::withItems([]);
        }
        return $this->messaging->sendMulticast($message, $targets);
    }

    /**
     * @param array $targets
     */
    protected function validateTargets(array $targets): void
    {
        foreach ($targets as $target) {
            if (!is_string($target)) {
                throw new ChannelException(sprintf('Notification target must be string, %s given', gettype($target)));
            }
        }
    }
}
