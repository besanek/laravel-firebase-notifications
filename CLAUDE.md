# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/FirebaseTest.php

# Run a single test method
vendor/bin/phpunit --filter testEmptyTarget

# Install dependencies
composer install
```

## Architecture

This is a Laravel notification channel package (`besanek/laravel-firebase-notifications`) that bridges Laravel's notification system with Firebase Cloud Messaging (FCM) via the `kreait/laravel-firebase` SDK.

**Flow:** `Notifiable::notify()` → Laravel `ChannelManager` → `FirebaseChannel::send()` → `kreait` Messaging SDK → FCM

**Key files:**
- `src/FirebaseServiceProvider.php` — registers `FirebaseChannel` as a singleton and extends Laravel's `ChannelManager` with the `'firebase'` driver
- `src/FirebaseChannel.php` — the channel implementation; calls `toFirebase()` on the notification, resolves targets via `routeNotificationForFirebase()`, validates they are strings, then sends via `messaging->sendMulticast()`
- `src/Exceptions/ChannelException.php` — thrown when `toFirebase()` is missing, returns wrong type, or targets are not strings

**How the channel is wired:** `FirebaseServiceProvider` calls `$channelManager->extend('firebase', ...)` so that notifications using `via() = ['firebase']` are routed to `FirebaseChannel`.

**Notifiable contract:** The notifiable entity must implement `routeNotificationForFirebase()` returning a single device token (string) or an array of strings. Empty/null targets result in a no-op (empty `MulticastSendReport`).

**Notification contract:** The notification must implement `toFirebase(): Kreait\Firebase\Messaging\Message` (typically `CloudMessage`).

## Tests

Tests are **integration tests** that make real HTTP calls to the Firebase API. Credentials are hardcoded in `phpunit.xml` as the `FIREBASE_CREDENTIALS` env variable. Tests use fake device IDs and assert that Firebase returns `NotFound` errors — they do not mock the Firebase SDK.

Test infrastructure uses Orchestra Testbench (`tests/TestCase.php`) which registers both `kreait/laravel-firebase`'s `ServiceProvider` and `FirebaseServiceProvider`.
