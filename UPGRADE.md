# Upgrading

## Unreleased

### The queue no longer carries a tenant between jobs   ⚠️ behaviour change

**What changed.** A queue worker used to keep whatever tenant the previous job
activated. A job with no `website_id` inherited it, a finished job left it
active, a job that threw left it active, and a job naming a deleted website
inherited whichever tenant ran before it.

Now the active tenant is scoped to the job: it is released when a job declares
none, and restored to whatever was active beforehand when a job ends or fails.

**How this can affect you.** Jobs that quietly relied on inheriting an ambient
tenant will start failing with a connection error instead of reading and
writing another tenant's database. That is the point of the change, but it does
mean previously silent behaviour becomes a visible failure.

**What to do.** Make sure every job that touches tenant data is dispatched from
within that tenant's context, so its payload carries `website_id`. Jobs that
belong to the system should not touch models using `UsesTenantConnection`.

**Escape hatch.** `TENANCY_QUEUE_RESET_TENANT=false` restores the old
behaviour while you adapt. It is not recommended: the old behaviour leaks
tenant context between jobs, on a connection that is valid and authenticated,
so nothing errors and nothing is logged.

**Not affected.** Synchronous dispatch. `dispatch_sync` runs inside the request
that asked for it, and `DispatcherMiddleware` deliberately switches the ambient
tenant there. That behaviour is unchanged.

### Connection::set(null) now clears the connection configuration

**What changed.** Releasing the tenant with `Connection::set(null)` used to
close the connection but leave the previous tenant's database, user and
password in `config('database.connections.tenant')`. The next model using
`UsesTenantConnection` reopened it straight into that tenant's database. It now
purges the configuration as well.

**What to do.** Nothing, unless you relied on the connection configuration
surviving a release, which was never safe.

### New: Environment::forgetTenant()

There was no supported way to say "no tenant is active".
`Environment::tenant(null)` reads as releasing one but does nothing: the null
branch simply returns whatever is currently active. `forgetTenant()` releases
it and emits `Events\Websites\Forgotten`.

### New: Events\Websites\Forgotten

The counterpart to `Websites\Switched`. Listeners that set up per-tenant state
when a tenant becomes active should tear it down here. It carries no website on
purpose: the point is that there is not one.

### New configuration key

```php
'queue' => [
    'reset-tenant-between-jobs' => env('TENANCY_QUEUE_RESET_TENANT', true),
],
```
