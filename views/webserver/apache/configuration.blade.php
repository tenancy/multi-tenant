#
#   Auto generated Apache configuration
#       @time: {{ date('H:i:s d-m-Y') }}
#       @author: hyn-me/webserver
#       @website: {{ $website->id }} "{{ $website->present()->name }}"
#

#
#   Hostnames with certificate
#
@foreach($website->hostnamesWithCertificate as $hostname)
    @include('webserver::webserver.apache.includes.server-block', [
        'hostname' => $hostname,
        'ssl' => $hostname->certificate
    ])
@endforeach

#
#   Hostnames without certificate
#
@if($website->hostnamesWithoutCertificate->count() > 0)
    @include('webserver::webserver.apache.includes.server-block', [
        'hostnames' => $website->hostnamesWithoutCertificate
    ])
@endif


<Directory "{{ base_path() }}">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>