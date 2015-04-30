# HynMe multi tenancy

This package allows for multi tenancy websites on one installation of Laravel.

### Requirements

All packages of HynMe (including multi tenancy) require Laravel 5 and up.

### Installation

Register the service provider in your config/app.php:

```php
/*
 * Hyn Wm packages
 */
'HynMe\MultiTenant\MultiTenantServiceProvider',
```

To support multi tenancy in other (3rd party) packages, __replace__ the class alias for Eloquent:

```php
'Eloquent'  => 'HynMe\Framework\Models\AbstractModel',
```
