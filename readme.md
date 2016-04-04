# Multi tenancy

[![Latest Stable Version](https://poser.pugx.org/hyn/multi-tenant/v/stable)](https://packagist.org/packages/hyn/multi-tenant)
[![License](https://poser.pugx.org/hyn/multi-tenant/license)](https://packagist.org/packages/hyn/multi-tenant)
[![Build Status](https://travis-ci.org/hyn/multi-tenant.svg?branch=master)](https://travis-ci.org/hyn/multi-tenant)
[![Code Coverage](https://img.shields.io/codecov/c/github/hyn/multi-tenant.svg)](https://codecov.io/github/hyn/multi-tenan
t)
[![StyleCI](https://styleci.io/repos/39585488/shield)](https://styleci.io/repos/39585488)
[![Reference Status](https://www.versioneye.com/php/hyn:multi-tenant/reference_badge.svg?style=flat)](https://www.versioneye.com/php/hyn:multi-tenant/references)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f8241f3b-ab7b-4a58-9123-488c13560887/mini.png)](https://insight.sensiolabs.com/projects/f8241f3b-ab7b-4a58-9123-488c13560887)

This package allows for multi tenancy websites on one installation of Laravel.

---

The goals for this and its related packages are:

- Unobtrusive multi tenancy for Laravel 5.1 LTS.
- Provide proper insight into tenants and webserver.
- Powerful, but flexible for developers, use it the way you want.

### Reading material:

- [documentation][7]
- [changelog](changelog.md)
- [website][1]

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

For more information visit the [documentation](https://hyn.readme.io/docs/hyn-approach-to-tenancy).

### Example tenant website & demo

One website running on the multi tenant installation of [hyn.me][1] is [dummy.hyn.me](http://dummy.hyn.me), you can review the code on [github.com/hyn/dummy-tenant-website](https://github.com/hyn/dummy-tenant-website).

## [Installation, configuration][7]

## Chat or critical bug

- For priority bug reports, please contact me directly on [gitter.im/luceos][6].
- The package chat can be found at [gitter.im/hyn/multi-tenant][8].
- A support forum will be made available in the future.

## Q&A

> Please visit the [FAQ](https://hyn.readme.io/docs/frequently-asked-questions) in the [documentation][7].


[1]: https://hyn.me
[2]: https://hyn.me/packages/multi-tenant
[3]: https://github.com/hyn/multi-tenant
[5]: https://github.com/hyn/multi-tenant/issues
[6]: https://gitter.im/luceos
[7]: https://hyn.readme.io
[8]: https://gitter.im/hyn/multi-tenant
