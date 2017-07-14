#
#   Auto generated Apache configuration
#       @time: {{ date('H:i:s d-m-Y') }}
#       @author: hyn/multi-tenant
#       @website: {{ $website->uuid }}
#

@foreach($website->hostnames as $hostname)
    @include('tenancy.generators::webserver.apache.blocks.server', [
        'hostname' => $hostname,
        'website' => $website
    ])
@endforeach
