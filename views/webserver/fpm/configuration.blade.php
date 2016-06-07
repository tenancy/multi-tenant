;#
;#   Auto generated Fpm configuration
;#       @time: {{ date('H:i:s d-m-Y') }}
;#       @author: hyn.me
;#       @website: {{ $website->id }} "{{ $website->present()->name }}"
;#

;# unique fpm group
[{{ $website->id }}-{{ $website->present()->urlName }}]

;# listening for nginx proxying
listen=/var/run/php5-fpm.hyn-{{ $config['port'] + $website->id }}.sock
listen.allowed_clients=127.0.0.1


;# user under which the application runs
user={{ $website->websiteUser }}

;# group under which the application runs
group={{ config('webserver.group', 'users') }}

;# fpm pool management variables
pm=dynamic
pm.max_children         = 20
pm.start_servers        = 5
pm.min_spare_servers    = 5
pm.max_spare_servers    = 10
pm.max_requests         = 20

;# force fpm workers into the following path
chdir                   = {{ base_path() }}