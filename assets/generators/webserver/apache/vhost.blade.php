#
#   Auto generated Apache configuration
#       @time: {{ date('H:i:s d-m-Y') }}
#       @author: hyn/multi-tenant
#       @website: {{ $website->uuid }}
#

#
#   Hostnames with certificate
#
@foreach($website->hostnamesWithCertificate as $hostname)
    @include('tenancy.generator::webserver.apache.blocks.server', [
        'hostname' => $hostname,
        'ssl' => $hostname->certificate
    ])
@endforeach

#
#   Hostnames without certificate
#
@if($website->hostnamesWithoutCertificate->count() > 0)
    @include('tenancy.generator::webserver.apache.blocks.server', [
        'hostnames' => $website->hostnamesWithoutCertificate
    ])
@endif
