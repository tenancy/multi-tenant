<?php namespace HynMe\MultiTenant\Commands;

use Illuminate\Console\Command;

class TenantCommand extends Command
{
    protected $signature = 'multi-tenant {action} {website? : The ID of the tenant website to run actions on}';

    public function handle()
    {

    }
}