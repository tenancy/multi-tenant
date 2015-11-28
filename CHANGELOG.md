# Changelog

All notable changes to `hyn/multi-tenant` will be documented in this file.

---

### 0.9.1

- Added support for tenants in one database using table prefixes.
- Seeder now working with tenants.
- Abstracted code used in migrations and seeds to separate trait.

### 0.9.0

- Dropped support for PHP 5.5 in automated tests by travis-ci.
- Fixed the issue with the tenant database not being created during setup.
- Technical debt removed with testing, caused by including the whole package again as dependency of laravel.

### 0.8.0

- Moved from Laraflock to Hyn namespace for main multi-tenant package. Require dependency by using `hyn/multi-tenant`.

### 0.7.5

- Updated to latest phpunit.
- Fixed helper function tenant_path to use the argument.

### 0.7.4

- Fixed references to older namespaces.
- Updated readme to reflect namespace changes.
- First commit for database seeder.
- Several StyleCI fixes.

### 0.7.3 (beta 3)

- Stabilization completed, the added installer script helps on proving this point.

### 0.6.0 (beta 2)

- Testing completed, the webserver dependency is not yet stable.

### 0.5.3

> The changelog has been hardly updated since 0.4.3; my apologies for this. My goal has been to stabilize
the package and to guarantee it can be deployed in working order. This goal will remain until the first
stable release 1.0.0. At this time the tests all point out the package works, but not all code nor
all integration testing has been completely written.

### 0.4.3

- Moved to namespace Laraflock.

### 0.4.2

- Preliminary release now available, let's call this a _Release Candidate 1_
- Migration to LaraLeague complete, fixed [readme.md](readme.md) and unit test in travis

### 0.4.1

- Namespace changed to LaraLeague
- Fixed tenant migrations and unit tests to validate that functionality

### 0.4.0

- Basic implementation of migrating for all or separate tenants.

### 0.3.1

- General system now works, supports multi tenancy globally for Laravel 5.1 LTS.
