# Multi tenancy

[![Latest Stable Version](https://poser.pugx.org/laraflock/multi-tenant/v/stable)](https://packagist.org/packages/laraflock/multi-tenant)
[![License](https://poser.pugx.org/laraflock/multi-tenant/license)](https://packagist.org/packages/laraflock/multi-tenant)
[![Build Status](https://travis-ci.org/laraflock/multi-tenant.svg?branch=master)](https://travis-ci.org/laraflock/multi-tenant)
[![Code Coverage](https://img.shields.io/codecov/c/github/laraflock/multi-tenant.svg)](https://codecov.io/github/laraflock/multi-tenant)
[![StyleCI](https://styleci.io/repos/39585488/shield)](https://styleci.io/repos/39585488)

> Please note this package is currently available as beta up until version 1.0.0. A working version can be seen in production on [hyn.me](http://hyn.me).
> This package is compatible only with Laravel __5.1 LTS__.
> If you have questions or wish to participate reach out to any laraflock developer.

This package allows for multi tenancy websites on one installation of Laravel. 

The goals for this and its related packages are:

- Unobtrusive multi tenancy for Laravel 5.1 LTS
- Provide proper insight into tenants and webserver
- Flexibility for developers, use it the way you want

### Reading material:

- [changelog](CHANGELOG.md)
- [todo, contribute](https://trello.com/b/vIROJOMC/multi-tenant)
- [website](http://hyn.me)

## What is multi tenancy

Referring to [wikipedia](http://en.wikipedia.org/wiki/Multitenancy);

> Multitenancy refers to a software architecture in which a single instance of a software runs on a server and serves multiple tenants.

### Multi tenancy how?

In its most abstract sense you can use this package to manage multiple websites with only one application installation.
- Multiple websites running on one code base.
- Multiple hostnames configured per website.

Each website has its own folder on disk, allowing:
- seperation of routes, templates, translations etc
- custom files (media, themes and packages)

Also each website has its own database, this ensures that in no way one website can access data from another website.
The distinction also gives proper division of responsibilities to the system (global) and tenant (local) databases.

For more information visit the [hyn package page][2].

### Example tenant website & demo

One website running on the multi tenant installation of [hyn.me][1] is [dummy.hyn.me](http://dummy.hyn.me).

A demo showing the back-end will be available soon.

## Requirements

All packages for multi tenancy require Laravel 5.1 LTS.

## Installation

### Composer

Include the dependancy in your composer.json:

```
composer require laraflock/multi-tenant
```

### Service provider

Register the service provider in your `config/app.php` within the `providers` array:

```php
/*
 * HynMe packages
 * @info FrameworkServiceProvider will load any available Service Provider from other hyn-me packages
 */
HynMe\Framework\FrameworkServiceProvider::class,
```
> Please note this says __FrameworkServiceProvider__ from the __Framework__ package, registering the __MultiTenantServiceProvider__ will break multi tenancy features!

### Third party eloquent models (optional)

To support multi tenancy in other (3rd party) packages, __replace__ the class alias for Eloquent under `aliases` in your `config/app.php`:

```php
'Eloquent'  => HynMe\Framework\Models\AbstractModel::class,
```

This will ensure that all extended classes will by default connect with the tenant database instead of the system database.
If you want to manually connect to the tenant database, set the `$connection` property of your class to `tenant`.

### Queue

Hyn uses the queue heavily to generate config files, create databases and unix users without blocking the application. In order to work properly setup the queue functionality.

- [Laravel documentation on queues](http://laravel.com/docs/5.1/queues)
- How to setup a queue using [beanstalkd](https://laracasts.com/lessons/beanstalkd-queues-with-laravel), [iron.io](https://laracasts.com/lessons/ironclad-queues).

Please note the queue __has to__ run as root for configuration files and other task to be run without issues.

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
php artisan multi-tenant:setup --tenant=EXAMPLE --email=foo@example.com --hostname=example.com --webserver=(nginx|apache|no)
```

Either run this command as root or under sudo if you want to configure your webserver service as well. Currently supported are apache2 and nginx.

Please note, if you decide to skip the configuration of the webserver you will have to configure it by yourself. Example files are generated in the `storage/webserver` directories.

## Chat or critical bug

If you'd like to hang out with us or would like to discuss a critical vulnerability; please contact us on [laraflock][4] or [the slack community of laravel](http://larachat.slack.com).

## Q&A

Q: How do you pronounce hyn?
> A: You would pronounce it just like `hine` with the same sound as `dine`.
 
Q: Why not use/help/extend [AuraEQ](https://github.com/AuraEQ/laravel-multi-tenant)?
> A: AuraEQ is different in comparison to hyn in the sense that it uses the same database with specific columns per table to identify different tenants. Hyn aims to keep tenants seperated by giving a tenant website it's own database, disk folder, routes, vendor packages etc.

Q: Why not use/help/extends [tenanti](https://github.com/orchestral/tenanti)?
> A: One primary goal of hyn is to remain unobtrusive, meaning you should use the package the way you want, without the need to completely change how you code/work/play. Also I think auto selecting the tenant website based on the configured hostnames is easier for website development companies to work with.

Q: Why do you need root or sudo to run the setup or the queue?
> A: Sudo or root is only required to register the webserver configuration files into the webserver services. Running the queue under root allows the tasks to immediately update the webserver once new configuration files are written.

Q: Will you make this package paid in the future?
> A: No. If any commercial move takes place, it will be at least a [freemium](https://en.wikipedia.org/wiki/Freemium) pricing model where additional, __optional__ packages will be made available for a fee. The core packages will always remain available under the MIT license.

Q: I have a bug, feature request or technical question.
> A: Visit the [issues page][3] on github.

Q: I have need for more direct support, advice or consultation for implementation.
> A: Contact me or other experienced implementation developers of [Laraflock][4].

Q: Why does the user for the `hyn` connection need `grant` rights?
> A: In order for hyn to create databases and give each tenant website its own database user, it needs to be allowed to grant those rights to dynamically generated users?

Q: Is hyn multi tenancy more vulnerable to hacking?
> A: Using this package will not make your application more open to attacks. For instance the laravel application generates a random hash after installation, hyn uses this unique hash for generating tenant database passwords.

Q: Are these hyn packages a CMS?
> A: No. The packages are meant for developers or development companies who want to run identical code on several websites, without the need to duplicate the code. This while also allowing for per-website different settings, vendor packages etc.

Q: Hooking apache config files to OSX apache webservice?
> A: Edit `/etc/apache2/httpd.conf` and at the bottom at a line `Include /<laravel installation>/storage/webserver/apache/*.conf`. Now reload or restart apache.


[1]: https://hyn.me
[2]: https://hyn.me/packages/multi-tenant
[3]: https://github.com/laraflock/multi-tenant
[4]: https://github.com/laraflock
