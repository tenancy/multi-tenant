# HynMe multi tenancy
[![Build Status](https://travis-ci.org/hyn-me/multi-tenant.svg?branch=master)](https://travis-ci.org/hyn-me/multi-tenant)

This package allows for multi tenancy websites on one installation of Laravel.

## What is multi tenancy

Referring to [wikipedia](http://en.wikipedia.org/wiki/Multitenancy);

> Multitenancy refers to a software architecture in which a single instance of a software runs on a server and serves multiple tenants.

### How has HynMe implemented multi tenancy

In its most abstract sense you can use hyn to manage multiple websites with only one application installation.
- Multiple websites running on one code base.
- Multiple hostnames configured per website.

Each website has its own folder on disk, allowing:
- seperation of routes, templates, translations etc
- custom files (media, themes and packages)

Also each website has its own database, this ensures that in no way one website can access data from another website.
The distinction also gives proper division of responsibilities to the system (global) and tenant (local) databases.

For more information visit the [hyn.me website](http://hyn.me).

## Requirements

All packages of HynMe (including multi tenancy) require Laravel 5 and up.

## Installation

### Register service provider

Register the service provider in your config/app.php:

```php
/*
 * HynMe packages
 */
'HynMe\MultiTenant\MultiTenantServiceProvider',
```

### Support third party eloquent models

To support multi tenancy in other (3rd party) packages, __replace__ the class alias for Eloquent:

```php
'Eloquent'  => 'HynMe\Framework\Models\AbstractModel',
```

This will ensure that all extended classes will by default connect with the tenant database instead of the system database.
If you want to manually connect to the tenant database, set the `$connection` property of your class to `tenant`.

### System database connection

In your `config/database.php` file make sure a database connection is configured with the name `system`. Also prevent any other connection
listed as `tenant`, this package will dynamically create a connection to the tenant database using that config identifier.

### Default/fallback hostname

As a last step edit your `.env` file in the root of your laravel installation and set a default hostname. 

```txt
HYN_MULTI_TENANCY_HOSTNAME=<hostname>
```

The entered hostname will be used to fallback if a hostname is hitting on the application that is unknown in the database,
thus showing the fallback website.