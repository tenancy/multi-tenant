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

### Releasing the tenant now releases its disk too   ⚠️ behaviour change

**What changed.** `ActivatesDisk` rooted the `tenant` disk in the active
tenant's directory when one was identified or switched to, and did nothing when
one was released. The disk stayed pointed at the tenant that had just been let
go, so anything writing to `Storage::disk('tenant')` afterwards landed in that
customer's files. It is the filesystem counterpart of the connection keeping the
previous tenant's credentials.

Releasing the tenant now clears the disk as well, which makes the next access
fail loudly rather than write to the wrong place.

**How this can affect you.** Code that wrote to `Storage::disk('tenant')`
outside any tenant's context used to succeed silently against whichever tenant
came last. It now throws. That is the point of the change, but it does turn
previously silent behaviour into a visible failure — most likely on a queue
worker, which releases the tenant between jobs.

**What to do.** Make sure anything touching `disk('tenant')` runs inside the
context of the tenant it belongs to.

### tenancy:run puts back the tenant it found   ⚠️ behaviour change

**What changed.** The command switched tenant on each website in turn and left
the last one active when it finished, and on the way out of an exception. Run
from a terminal that hardly matters, since the process ends. Called from a
request or a job through `Artisan::call('tenancy:run')`, the caller silently
carried on as a customer it never asked for.

It now restores whatever tenant was active beforehand, or releases it when
there was none.

**Not affected.** The commands built on `processHandle()` —
`tenancy:migrate` and friends — keep their current behaviour: with a single
tenant in the chunk the connection is deliberately left active, and the suite
asserts it.

### tenancy:migrate:fresh no longer wipes the whole database in prefix mode   ⚠️ behaviour change

**What changed.** The command called `db:wipe` on the tenant connection, which
empties every table in the database behind it. In the `database` and `schema`
division modes that database belongs to the tenant alone, so that was right. In
`prefix` mode all tenants and the system tables share one database, so running
it dropped **every tenant's tables along with `websites` and `hostnames`**. The
damage was visible from inside the command itself, whose next page of websites
was read from the table it had just dropped.

It now drops only the tables carrying that tenant's prefix.

**How this can affect you.** If you use `prefix` mode, this command was
destroying your installation and you very likely never ran it twice. Nothing you
relied on changes; it simply stops taking everything else with it.

**Views and user defined types are not dropped** in `prefix` mode. `--drop-views`
and `--drop-types` are honoured in the modes that give the tenant a database of
its own, where dropping everything is safe. In a shared database there is no
prefix on those objects to tell whose they are.

**In the `bypass` division mode the command now refuses to run.** There, the
tenant connection *is* the system connection and nothing marks a tenant's tables
apart, so any wipe would take the system with it. It fails with an explanation
instead.

**Not affected.** The `database` and `schema` division modes, which keep calling
`db:wipe` exactly as before.

### PostgreSQL 15 and newer can provision tenants again   ⚠️ behaviour change

**What changed.** PostgreSQL 15 revoked the `CREATE` privilege that the `public`
schema used to hand to every role. The driver only ever granted privileges on
the *database*, which on 15 and newer is no longer enough to create a table, so
a tenant database was created successfully and then failed every migration with
`permission denied for schema public`. Provisioning now also grants on the
schema, from a connection to the new database, because a schema grant has no
effect from anywhere else.

Two things follow from that grant. It is issued to `PUBLIC` rather than to the
tenant role, since a role level grant is recorded as a dependency and would make
`DROP USER` fail when the tenant is deleted. And to keep that from widening
anything, `CONNECT` is now revoked from `PUBLIC` on each new tenant database.

**How this can affect you.** Any role that used to reach a tenant database
purely through the default `PUBLIC` connect privilege will be refused on
databases created from now on. Reading rows was already refused, but listing
tables was not. Roles that were granted access explicitly, the tenant itself,
and the owner of the database are unaffected.

Backup, monitoring or reporting roles are the ones to check. Grant them what
they need explicitly:

```sql
GRANT CONNECT ON DATABASE "<tenant uuid>" TO "<your role>";
```

**Databases created before this change do not gain the revoke retroactively.**
They keep working exactly as before. To apply the same boundary to them, run
this once per existing tenant database:

```sql
REVOKE CONNECT ON DATABASE "<tenant uuid>" FROM PUBLIC;
```

**Not affected.** The `schema` division mode, which grants on its own schema and
never relied on `public`. MySQL and MariaDB.

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
