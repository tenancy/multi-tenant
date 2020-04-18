#
#   Auto generated Nginx configuration
#       @author: tenancy/multi-tenant
#       @see: https://tenancy.dev
#       @time: {{ date('H:i:s d-m-Y') }}
#       @website id: {{ $website->id }}
#       @website uuid: {{ $website->uuid }}
#

@forelse($website->hostnames as $hostname)
    @include('tenancy.generators::webserver.nginx.blocks.server', [
        'hostname' => $hostname,
        'website' => $website,
        'media' => $media
    ])
@empty
    #
    #   No hostnames found
    #
@endforelse
