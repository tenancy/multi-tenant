#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev

# set symlink so it seems as if this is a factual laravel installation
ln -s vendor vendor/laravel/laravel/vendor

# Set up supervisor and the beanstalk queue

cat /etc/default/beanstalkd | sed -e "s/#START=yes/START=yes/" > /tmp/beanstalkd
sudo mv -f /tmp/beanstalkd /etc/default/beanstalkd

cat <<EOF > /tmp/supervisor
[program:travis-queue]
command=php artisan queue:work default --env=testing --daemon
process_name=travis_queue
numprocs=1
autostart=1
autorestart=1
user=root
directory=$TRAVIS_BUILD_DIR/vendor/laravel/laravel
stdout_logfile=/var/log/travis-queue.log
redirect_stderr=true
EOF
sudo mv -f /tmp/supervisor /etc/supervisor/conf.d/laravel-queue.conf

sudo service beanstalkd start
sudo supervisorctl reread
sudo supervisorctl update

# moves the unit test to the root laravel directory
cp phpunit.travis.xml phpunit.xml

phpunit --coverage-text --coverage-clover=coverage.clover
