# Laravel Firebase Notifications

Thin layer for integrate Firebase Messaging as Laravel Notifications

## Requirement

-   PHP >= 8.1
-   Laravel = 10.x

## Installing

```shell
$ composer require "besanek/laravel-firebase-notifications"
```

This is all, thanks to the Package Autodiscover.

## Setup

Please follow configuration guide of [kreait/laravel-firebase](https://github.com/kreait/laravel-firebase#configuration)


## Basic Usage

Add new method `routeNotificationForFirebase` to your notifiable entity, witch returns device id.

```php
    /**
     * It could be one device
     */
    public function routeNotificationForFirebase()
    {
        return $this->device_id;
    }
    
    /**
     * Or you can return array for multicast
     */
    public function routeNotificationForFirebase()
    {
        return $this->devices()->get()->pluck('id');
    }
```

In Notification entity you should add *firebase* to `via()` method.

```php
    public function via(): array
    {
        return ['firebase', /* 'email', 'database', 'etc...'*/];
    }
```

And you can construct `CloudMessage` into `toFirebase()` method.

```php
    public function toFirebase(): Messaging\CloudMessage
        $notification = Messaging\Notification::create('I <3 laravel', 'It is true');
        return Messaging\CloudMessage::new()->withNotification($notificatin);
    }
```
Please look into the [official PHP SDK documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#adding-a-notification) for the full use of all possibilities.
