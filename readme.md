# HynMe multi tenancy
[![Build Status](https://travis-ci.org/hyn-me/multi-tenant.svg?branch=master)](https://travis-ci.org/hyn-me/multi-tenant)

This package allows for multi tenancy websites on one installation of Laravel.

### What is multi tenancy

Referring to [wikipedia](http://en.wikipedia.org/wiki/Multitenancy);

> Multitenancy refers to a software architecture in which a single instance of a software runs on a server and serves multiple tenants.

### How has HynMe implemented multi tenancy

In its most abstract sense you can use hyn to manage multiple websites with only one application installation.
- Multiple websites running on one code base.
- Multiple hostnames configured per website.
- Each website has its own folder, allowing for custom files (images, local plugins and themes).
- Each website has its own database, allowing system and tenant information to be kept separate.

For more information visit the official [hyn website](http://hyn.me).

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
