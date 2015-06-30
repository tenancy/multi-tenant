# Hyn multi tenancy

[![Join the chat at https://gitter.im/hyn-me/multi-tenant](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/hyn-me/multi-tenant?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Latest Stable Version](https://poser.pugx.org/hyn-me/multi-tenant/v/stable)](https://packagist.org/packages/hyn-me/multi-tenant)
[![License](https://poser.pugx.org/hyn-me/multi-tenant/license)](https://packagist.org/packages/hyn-me/multi-tenant)
[![Build Status](https://travis-ci.org/hyn-me/multi-tenant.svg?branch=master)](https://travis-ci.org/hyn-me/multi-tenant)


> Please note this package is under development. A working (closed source) multi tenant version is currently in production for laravel 4.1. These packages are an open-source refactoring for version 5 of laravel.
> If you have questions or wish to participate reach out to me in any way.

This package allows for multi tenancy websites on one installation of Laravel. 

The goals for this and its related packages are:

- Unobtrusive multi tenancy for Laravel 5+
- Provide proper insight into tenants and webserver

For more information visit the [hyn.me website](http://hyn.me).

## What is multi tenancy

Referring to [wikipedia](http://en.wikipedia.org/wiki/Multitenancy);

> Multitenancy refers to a software architecture in which a single instance of a software runs on a server and serves multiple tenants.

### How has hyn implemented multi tenancy

In its most abstract sense you can use hyn to manage multiple websites with only one application installation.
- Multiple websites running on one code base.
- Multiple hostnames configured per website.

Each website has its own folder on disk, allowing:
- seperation of routes, templates, translations etc
- custom files (media, themes and packages)

Also each website has its own database, this ensures that in no way one website can access data from another website.
The distinction also gives proper division of responsibilities to the system (global) and tenant (local) databases.

For more information visit the [hyn.me website](http://hyn.me).

### Example tenant website & demo

One website running on the multi tenant installation of [hyn.me](http://hyn.me) is [dummy.hyn.me](http://dummy.hyn.me).

A demo showing the back-end will be available soon.

## Requirements

All packages of hyn (including multi tenancy) require Laravel 5 and up.

## Installation

### Composer

Include the dependancy in your composer.json:

```
composer require hyn-me/multi-tenant
```

### Service provider

Register the service provider in your `config/app.php` within the `providers` array:

```php
/*
 * HynMe packages
 * @info FrameworkServiceProvider will load any available Service Provider from other hyn-me packages
 */
'HynMe\Framework\FrameworkServiceProvider',
```
> Please note this says __FrameworkServiceProvider__ from the __Framework__ package, registering the __MultiTenantServiceProvider__ will break multi tenancy features!

### Third party eloquent models (optional)

To support multi tenancy in other (3rd party) packages, __replace__ the class alias for Eloquent under `aliases` in your `config/app.php`:

```php
'Eloquent'  => 'HynMe\Framework\Models\AbstractModel',
```

This will ensure that all extended classes will by default connect with the tenant database instead of the system database.
If you want to manually connect to the tenant database, set the `$connection` property of your class to `tenant`.

### System database connection

In your `config/database.php` file make sure a database connection is configured with the name `hyn`. Also prevent any other connection
listed as `tenant`, this package will dynamically create a connection to the tenant database using that config identifier.

The system connection must have the rights to create, alter and delete users and databases and grant rights to others. This behavior is almost identical to a root (admin) user.
For security reasons do not configure that user for this connection. You could create such a user manually using:

```sql

create database `hyn_multi_tenancy`;
create user `hyn`@'localhost' identified by '<your_strong_random_string>';
grant all privileges on *.* to 'hyn'@'localhost' with grant option;
```

Using the above snippet you would then add in your config `database.php` as `hyn` key under `connections`:

```php

        'hyn' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'hyn_multi_tenancy',
            'username'  => 'hyn',
            'password'  => '<your_strong_random_string>',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],
```

In case you're wondering, you can still set the `hyn` connection as your `default` in the `database.php`. In order to be as unobtrusive as possible this is not forced for any hyn package.

### Default/fallback hostname

As a last step edit your `.env` file in the root of your laravel installation and set a default hostname. 

```txt
HYN_MULTI_TENANCY_HOSTNAME=<hostname>
```

The entered hostname will be used to fallback if a hostname is hitting on the application that is unknown in the database,
thus showing the fallback website. If you don't define this environment variable, a backtrace is generated.

### Run the setup

Go into your terminal and run the following artisan command to finish installation of multi tenancy.

```bash
php artisan multi-tenant:setup --tenant=EXAMPLE --email=foo@example.com --hostname=example.com
```

Please note, if you decide to skip the configuration of the webserver you will have to configure it by yourself. Example files are generated in the `storage/webserver` directories.

## Facts

Q: How do you pronounce hyn?
A: You would pronounce it just like `hine` with the same sound as `dine`.
 
Q: Why not use/help/extend [AuraEQ](https://github.com/AuraEQ/laravel-multi-tenant)?
A: AuraEQ is different in comparison to hyn in the sense that is uses the same database with specific columns per table to identify different tenants. Hyn aims to keep tenants seperated by giving a tenant website it's own database, disk folder, routes, vendor packages etc.

Q: Why not use/help/extends [tenanti](https://github.com/orchestral/tenanti)?
A: One primary goal of hyn is to remain unobtrusive, meaning you should use the package the way you want, without the need to completely change how you code/work/play. Also I think auto selecting the tenant website based on the configured hostnames is easier for website development companies to work with.

Q: Why do you need root or sudo to run the setup or the queue?
A: Sudo or root is only required to register the webserver configuration files into the webserver services. Running the queue under root allows the tasks to immediately update the webserver once new configuration files are written.

Q: Will you make this package paid in the future?
A: No. If any commercial move takes place, it will be at least a [freemium](https://en.wikipedia.org/wiki/Freemium) where additional, __optional__ packages will be made available for a fee. The core packages will always remain available under the MIT license.

Q: I have a bug, feature request or technical question.
A: Visit the [issues page](http://github.com/hyn-me/multi-tenant/issues) on github.

Q: I have need for more direct support, advice or consultation for implementation.
A: Contact me or other experienced implementation developers on [gitter](https://gitter.im/hyn-me/multi-tenant).