#
#   Auto generated Apache configuration
#       @author: hyn/multi-tenant
#       @see: http://laravel-tenancy.com
#       @time: {{ date('H:i:s d-m-Y') }}
#       @website id: {{ $website->id }}
#       @website uuid: {{ $website->uuid }}
#

@foreach($website->hostnames as $hostname)
    @include('tenancy.generators::webserver.apache.blocks.server', [
        'hostname' => $hostname,
        'website' => $website
    ])
@endforeach
