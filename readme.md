[![build status](https://gitlab.com/hyn-me/multi-tenant/badges/3.x/build.svg)](https://gitlab.com/hyn-me/multi-tenant/commits/3.x)
[![coverage report](https://gitlab.com/hyn-me/multi-tenant/badges/3.x/coverage.svg)](https://gitlab.com/hyn-me/multi-tenant/commits/3.x)

## Installation

Register the service provider in your `config/app.php`:

```php
    'providers' => [
        // [..]
        // Hyn multi tenancy.
        Hyn\Tenancy\Providers\TenancyProvider::class,
        // Hyn multi tenancy webserver integration.
        Hyn\Tenancy\Providers\WebserverProvider::class,
    ],
```

First publish the configuration files so you can modify it to your needs:

```bash
php artisan vendor:deploy --tag tenancy
```

Open the `config/tenancy.php` and `config/webserver.php` file and modify to your needs.

Now run:

```bash
php artisan tenancy:install
```
This will run the required system database migrations.
