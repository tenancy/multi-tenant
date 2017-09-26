<VirtualHost *:{{ array_get($config, 'ports.http', 80) }}>
    ServerName {{ $hostname->fqdn }}

    # public path, serving content
    DocumentRoot {{ public_path() }}
    # default document handling
    DirectoryIndex index.html index.php

    @if($media)
        # media directory
        alias "/media/" "{{ $media }}"
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


@if(isset($ssl))
    <VirtualHost *:{{ array_get($config, 'ports.https', 443) }}>
        ServerName {{ $hostname->fqdn }}

        # public path, serving content
        DocumentRoot {{ public_path() }}
        # default document handling
        DirectoryIndex index.html index.php

        # allow cross domain loading of resources
        Header set Access-Control-Allow-Origin "*"

        # logging
        ErrorLog {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.error.log
        CustomLog {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.access.log combined

        # enable SSL
        SSLEngine On
        SSLCertificateFile {{ $ssl->pathCrt }}
        SSLCertificateChainFile {{ $ssl->pathCa }}
        SSLCertificateKeyFile {{ $ssl->pathKey }}

        <FilesMatch "\.(cgi|shtml|phtml|php)$">
            SSLOptions +StdEnvVars
        </FilesMatch>

        BrowserMatch "MSIE [2-6]" \
        nokeepalive ssl-unclean-shutdown \
        downgrade-1.0 force-response-1.0
        # MSIE 7 and newer should be able to use keepalive
        BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown

        <Directory "{{ base_path() }}">
            Options FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
@endif
