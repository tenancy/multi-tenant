[![Packagist](https://img.shields.io/packagist/v/hyn/multi-tenant.svg)]()
[![build status](https://gitlab.com/hyn-me/multi-tenant/badges/3.x/build.svg)](https://gitlab.com/hyn-me/multi-tenant/commits/3.x)
[![codecov](https://codecov.io/gh/hyn/multi-tenant/branch/3.x/graph/badge.svg)](https://codecov.io/gh/hyn/multi-tenant/branch/3.x)
[![Packagist](https://img.shields.io/packagist/dt/hyn/multi-tenant.svg)]()

The unobtrusive Laravel package that makes your app multi tenant. Serving 
multiple websites, each with one or more hostnames from the same codebase. But
with clear separation of assets, database and the ability to override logic per
tenant.

Suitable for marketing companies that like to re-use functionality
for different clients or start-ups building the next software as a
 service.

---

Offers:

- Integration with Laravel 5.3, 5.4 and the upcoming 5.5.
- MariaDB or PostgreSQL database drivers.
- Event driven, extensible architecture.  
- Close integration into the webserver.
- The ability to add tenant specific configs, code, routes etc.

Database separation methods:

- One system database and separated tenant databases (default).
- Table prefixed in the system database.
- Or .. manually, the way you want, by listening to an event.

## Requirements, recommended environment

- Linux based OS preferred.
- PHP 7.1+.
- Apache 2.4+, nginx support coming soon.
- MariaDB 10+ or PostgreSQL 9+; please note that MySQL won't work because it limits database usernames to 16 characters.

## Installation

### Laravel 5.3 and 5.4

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

### Laravel 5.5 and up

Using [auto discovery](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518), the
tenancy package will be auto detected by Laravel automatically. 

In case you want to disable webserver integration, set the `dont-discover` in your application 
composer.json, like so:

```json
{
    // ..
    "extra": {
        "laravel": {
            "dont-discover": "hyn/multi-tenant"
        }
    }
}
```

Then follow the instructions for Laravel 5.3 and 5.4 above to register the `Hyn\Tenancy\Providers\TenancyProvider`.

### Deploy configuration

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
