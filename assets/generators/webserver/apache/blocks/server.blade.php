<VirtualHost *:{{ Arr::get($config, 'ports.http', 80) }}>
    ServerName {{ $hostname->fqdn }}

    # public path, serving content
    DocumentRoot {{ public_path() }}
    # default document handling
    DirectoryIndex index.html index.php

    @if($media)
        # media directory
        alias "/media/" "{{ $media }}/"
    @endif

    # allow cross domain loading of resources
    Header set Access-Control-Allow-Origin "*"

    # logging
    ErrorLog {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.error.log
    CustomLog {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.access.log combined

    <Directory "{{ base_path() }}">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
