server {

    listen {{ Arr::get($config, 'ports.http', 80) }};

    # server hostnames
    server_name {{ $hostname->fqdn }};

    # allow cross origin access
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Request-Method GET;

    # root path of website; serve files from here
    root {{ public_path() }};
    index index.php;

    # logging
    access_log {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.access.log;
    error_log {{ storage_path('logs/')  }}{{ $hostname->fqdn }}.error.log notice;

    @if($media)
    location ~* ^/media/(.+)$ {
        alias {{ $media }}/$1;
    }
    @endif

    location / {
        index index.php;
        try_files $uri $uri/ $uri/index.php?$args /index.php?$args;
    }

    # pass the PHP scripts to FastCGI server from upstream phpfcgi
    location ~ \.php(/|$) {
        fastcgi_pass {{ Arr::get($config, 'php-sock') }};
        include fastcgi_params;

        fastcgi_split_path_info ^(.+\.php)(/.*)$;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
