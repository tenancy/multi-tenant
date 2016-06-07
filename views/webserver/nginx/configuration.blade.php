#
#   Auto generated Nginx configuration
#       @time: {{ date('H:i:s d-m-Y') }}
#       @author: hyn-me/webserver
#       @website: {{ $website->id }} "{{ $website->present()->name }}"
#

@if($website->directory->image_cache())
    proxy_cache_path {{ $website->directory->image_cache() }} levels=1:2 keys_zone=img_cache_hyn_mt_{{ $website->id }}:10m max_size=1G;
@endif

#
#   Hostnames with certificate
#
@foreach($website->hostnamesWithCertificate as $hostname)
    @include('webserver::webserver.nginx.includes.server-block', [
        'hostname' => $hostname,
        'ssl' => $hostname->certificate
    ])
@endforeach

#
#   Hostnames without certificate
#
@if($website->hostnamesWithoutCertificate->count() > 0)
    @include('webserver::webserver.nginx.includes.server-block', [
        'hostnames' => $website->hostnamesWithoutCertificate
    ])
@endif