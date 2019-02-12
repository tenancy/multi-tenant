<?php

namespace Hyn\Tenancy\Listeners\URL;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Events\Websites\Identified;
use Hyn\Tenancy\Events\Websites\Switched;
use Illuminate\Contracts\Events\Dispatcher;

class UpdateAppUrl
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen([Identified::class, Switched::class], [$this, 'force']);
    }

    /**
     * @param Identified|Switched $event
     */
    public function force($event)
    {
        if (config('tenancy.hostname.update-app-url', false)) {
            $scheme = request()->getScheme() ?? parse_url(config('app.url', PHP_URL_SCHEME));

            /** @var Hostname $hostname */
            $hostname = app()->make(CurrentHostname::class) ?? $event->website->hostnames->first();

            if ($hostname) {
                $url = sprintf('%s://%s', $scheme, $hostname->fqdn);

                config([
                    'app.url' => $url
                ]);

                URL::forceRootUrl($url);
            }
        }
    }
}
