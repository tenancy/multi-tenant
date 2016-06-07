

<VirtualHost *:{{ $config->port->http or 80 }}>
    @if(isset($hostname))
        ServerAdmin webmaster{{ "@" . $hostname->hostname }}
    @else
        ServerAdmin webmaster{{ "@" .  $hostnames->first()->hostname }}
    @endif

    @if(isset($hostname))
        ServerName {{ $hostname->hostname }}
    @else
        @foreach($hostnames->lists('hostname') as $i => $hostname)
            @if($i == 0)
                ServerName {{ $hostname }}
            @else
                ServerAlias {{ $hostname }}
            @endif
        @endforeach
    @endif

    # public path, serving content
    DocumentRoot {{ public_path() }}
    # default document handling
    DirectoryIndex index.html index.php

    @if($website->directory->media())
        # media directory
        alias "/media/" "{{ $website->directory->media() }}"
    @endif

    # allow cross domain loading of resources
    Header set Access-Control-Allow-Origin "*"

    # logging
    ErrorLog {{ $log_path }}.error.log
    CustomLog {{ $log_path }}.access.log combined

    <IfModule mod_fastcgi.c>
        AddType application/x-httpd-fastphp5 .php
        Action application/x-httpd-fastphp5 /php5-fcgi
        Alias /php5-fcgi /usr/lib/cgi-bin/php5-fcgi
        FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -socket /var/run/php5-fpm.hyn-{{ $fpm_port + $website->id }}.sock -pass-header Authorization
        <Directory /usr/lib/cgi-bin>
        Require all granted
        </Directory>
    </IfModule>
</VirtualHost>


@if(isset($ssl))
<VirtualHost *:{{ $config->port->https or 443 }}>

    @if(isset($hostname))
        ServerAdmin webmaster{{ "@" . $hostname->hostname }}
    @else
        ServerAdmin webmaster{{ "@" . $hostnames->first()->hostname }}
    @endif

    @if(isset($hostname))
        ServerName {{ $hostname->hostname }}
    @else
        @foreach($hostnames->lists('hostname') as $i => $hostname)
            @if($i == 0)
                ServerName {{ $hostname }}
            @else
                ServerAlias {{ $hostname }}
            @endif
        @endforeach
    @endif

    # public path, serving content
    DocumentRoot {{ public_path() }}
    # default document handling
    DirectoryIndex index.html index.php

    @if ($website->websiteUser)
        # user
{{--        RUidGid {{ $website->websiteUser }} {{ config('webserver.group', 'users') }}--}}
        # using mpm itk module; see http://mpm-itk.sesse.net/
        <IfModule mpm_itk_module>
            AssignUserId {{ $website->websiteUser }} {{ config('webserver.group', 'users') }}
        </IfModule>
    @endif

    @if($website->directory->media())
        # media directory
        alias "/media/" "{{ $website->directory->media() }}"
    @endif

    # allow cross domain loading of resources
    Header set Access-Control-Allow-Origin "*"

    # logging
    ErrorLog {{ $log_path }}.error.log
    CustomLog {{ $log_path }}.access.log combined

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

    <IfModule mod_fastcgi.c>
        AddType application/x-httpd-fastphp5 .php
        Action application/x-httpd-fastphp5 /php5-fcgi
        Alias /php5-fcgi /usr/lib/cgi-bin/php5-fcgi
        FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -socket /var/run/php5-fpm.hyn-{{ $fpm_port + $website->id }}.sock -pass-header Authorization
        <Directory /usr/lib/cgi-bin>
        Require all granted
        </Directory>
    </IfModule>
</VirtualHost>
@endif