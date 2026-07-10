# TaskHub Connector

Lets TaskHub manage this application through signed HTTP requests.

Add-only by design: installing it changes no existing behaviour, and
`composer remove eisall/taskhub-connector` restores the app exactly.
While `TASKHUB_SECRET` is unset the package registers nothing at all.

## Install

```sh
composer require eisall/taskhub-connector
php artisan vendor:publish --tag=taskhub-config
```

Then set the identity TaskHub generated when the app was registered:

```dotenv
TASKHUB_SECRET=...
TASKHUB_APP_SLUG=studyseco
```

Declare what TaskHub may manage in `config/taskhub.php`:

```php
'resources' => [
    'holidays' => [
        'model' => App\Models\Holiday::class,
        'rules' => [
            'store' => ['name' => 'required', 'date' => 'required|date'],
            'update' => [],
        ],
    ],
    'users' => [
        'model' => App\Models\User::class,
        'readonly' => true,
    ],
],
```

## Endpoints

All under `/_taskhub`, all requiring a valid `X-TaskHub-Signature`
(HMAC-SHA256 over `METHOD|/path|timestamp|nonce|sha256(body)`), a
timestamp within five minutes, and a never-before-seen nonce.

| Method | Path | |
| --- | --- | --- |
| GET | `/_taskhub/health` | liveness, versions |
| GET | `/_taskhub/schema` | declared resources, fields, casts |
| GET | `/_taskhub/resources/{resource}` | paginated list |
| POST | `/_taskhub/resources/{resource}` | create |
| GET | `/_taskhub/resources/{resource}/{id}` | show |
| PATCH | `/_taskhub/resources/{resource}/{id}` | update |
| DELETE | `/_taskhub/resources/{resource}/{id}` | delete |

Writes pass through the model's own `fillable`, casts, mutators and
events. The connector adds reach, not new rules.

## Compatibility

PHP 8.0.2+, Laravel 9 through 12 — one package for every EISALL app.
