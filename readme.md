## Installation

Register the service provider in your `config/app.php`:

```php
    'providers' => [
        // [..]
        // Hyn multi tenancy
        Hyn\Tenancy\Providers\TenancyProvider::class,
    ],
```

First publish the configuration files so you can modify it to your needs:

```bash
php artisan vendor:deploy --tag tenancy
```

Open the `config/tenancy.php` file and modify to your needs.

Now run:

```bash
php artisan tenancy:install
```
This will run the required system database migrations.
