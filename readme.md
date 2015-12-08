# Multi tenancy

[![Latest Stable Version](https://poser.pugx.org/hyn/multi-tenant/v/stable)](https://packagist.org/packages/hyn/multi-tenant)
[![License](https://poser.pugx.org/hyn/multi-tenant/license)](https://packagist.org/packages/hyn/multi-tenant)
[![Build Status](https://travis-ci.org/hyn/multi-tenant.svg?branch=master)](https://travis-ci.org/hyn/multi-tenant)
[![Code Coverage](https://img.shields.io/codecov/c/github/hyn/multi-tenant.svg)](https://codecov.io/github/hyn/multi-tenan
t)
[![StyleCI](https://styleci.io/repos/39585488/shield)](https://styleci.io/repos/39585488)
[![Reference Status](https://www.versioneye.com/php/hyn:multi-tenant/reference_badge.svg?style=flat)](https://www.versioneye.com/php/hyn:multi-tenant/references)

This package allows for multi tenancy websites on one installation of Laravel.

---

The goals for this and its related packages are:

- Unobtrusive multi tenancy for Laravel 5.1 LTS
- Provide proper insight into tenants and webserver
- Flexibility for developers, use it the way you want

### Reading material:

- [documentation][7]
- [changelog](CHANGELOG.md)
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

---

## Installation, configuration

> Please visit the [documentation][7].

---

## Chat or critical bug

If you'd like to hang out with us or would like to discuss a critical vulnerability; please contact me on [gitter][6].

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
> A: Visit the [issues page][5] on github.

Q: I have need for more direct support, advice or consultation for implementation.
> A: Contact me for additional support.

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
[3]: https://github.com/hyn/multi-tenant
[5]: https://github.com/hyn/multi-tenant/issues
[6]: https://gitter.im/hyn/multi-tenant
[7]: https://hyn.readme.io
