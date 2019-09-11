<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RunCommand extends Command
{
    use DispatchesEvents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:run {run : The artisan command to run for the tenants}
        {--tenant=* : The tenant(s) to run the command for}
        {--argument=* : Arguments to pass onto the command}
        {--option=* : Options to pass onto the command}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run commands on all tenant databases present in the system db.';

    /**
     * Execute the console command.
     *
     * @param Environment       $environment
     * @param WebsiteRepository $repository
     */
    public function handle(Environment $environment, WebsiteRepository $repository)
    {
        $query = $repository->query();

        if ($ids = $this->option('tenant')) {
            $query->whereIn('id', $ids);
        }

        $options = collect($this->option('option') ?? [])
            ->mapWithKeys(function ($value, $key) {
                list($key, $value) = explode('=', $value);

                return ["--$key" => $value];
            })
            ->merge($this->option('argument') ?? [])
            ->mapWithKeys(function ($value, $key) {
                if (!Str::startsWith($key, '--')) {
                    list($key, $value) = explode('=', $value);
                }

                return [$key => $value];
            });

        $exitCodes = [];

        $query->chunk(50, function ($websites) use ($environment, $options, &$exitCodes) {
            foreach ($websites as $website) {
                $environment->tenant($website);

                $exitCodes[] = $this->call(
                    $this->argument('run'),
                    $options->toArray()
                );
            }
        });

        if (count($exitCodes) === 0) {
            $this->warn("Command was executed on zero tenants.");
        }
    }
}
