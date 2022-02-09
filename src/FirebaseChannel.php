<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications;

use Besanek\LaravelFirebaseNotifications\Exceptions\ChannelException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MulticastSendReport;

class FirebaseChannel
{
    private Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function send($notifiable, Notification $notification): MulticastSendReport
    {
        if (!method_exists($notification, 'toFirebase')) {
            throw new ChannelException('Method toFirebase() is missing');
        }

        $message = $notification->toFirebase();

        if (!$message instanceof Message) {
            throw new ChannelException(
                'Channel expected return type %s, %s is returned', Message::class, gettype($message)
            );
        }

        $targets = $notifiable->routeNotificationFor('firebase', $notification);
        $targets = !$targets ? [] : Arr::wrap($targets);
        $this->validateTargets($targets);

        return $this->sendToTargets($message, $targets);
    }

    /**
     * @param array<string> $targets
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    protected function sendToTargets(Message $message, array $targets): MulticastSendReport
    {
        if (empty($targets)) {
            return MulticastSendReport::withItems([]);
        }
        return $this->messaging->sendMulticast($message, $targets);
    }

    /**
     * @param array<string> $targets
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
