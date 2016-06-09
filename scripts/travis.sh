#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev


# Set up supervisor and the beanstalk queue

cat /etc/default/beanstalkd | sed -e "s/#START=yes/START=yes/" > /tmp/beanstalkd
mv -f /tmp/beanstalkd /etc/default/beanstalkd

sudo cat <<EOF > /etc/supervisor/conf.d/laravel-queue.conf
[program:travis-queue]
command=php artisan queue:work default --env=testing --daemon
process_name=travis_queue
numprocs=1
autostart=1
autorestart=1
user=root
directory=$TRAVIS_BUILD_DIR/vendor/laravel/laravel
stdout_logfile=/var/log/queue.log
redirect_stderr=true
EOF

sudo service beanstalkd start
sudo supervisorctl reread
sudo supervisorctl update

# moves the unit test to the root laravel directory
cp phpunit.travis.xml phpunit.xml

phpunit --coverage-text --coverage-clover=coverage.clover
