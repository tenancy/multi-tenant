#
#   Auto generated Apache configuration
#       @author: hyn/multi-tenant
#       @see: http://laravel-tenancy.com
#       @time: {{ date('H:i:s d-m-Y') }}
#       @website id: {{ $website->id }}
#       @website uuid: {{ $website->uuid }}
#

@forelse($website->hostnames as $hostname)
    @include('tenancy.generators::webserver.apache.blocks.server', [
        'hostname' => $hostname,
        'website' => $website,
        'media' => $media
    ])
@empty
#
#   No hostnames found
#
@endforelse
