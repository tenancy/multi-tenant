#!/bin/bash

DATABASES="$(mysql -u root -Bse 'show databases')"
REGEX="^([a-z0-9]{8}\-?)([a-z0-9]{4}\-?){3}([a-z0-9]{12})"

for db in ${DATABASES}; do
    if [[ ${db} =~ ${REGEX} ]]; then
        echo "Deleting database ${db}"
        echo Y | mysqladmin -u root drop ${db}
    fi
done

mysql -u root -D "mysql" -NBe "select User, Host from user" | while read -r user host;
do
    if [[ ${user} =~ ${REGEX} ]]; then
            echo "Deleting user '${user}'@'${host}'"
            mysql -u root -Bse "drop user '${user}'@'${host}'"
    fi
done


echo Y | mysqladmin -u root drop tenancy
mysqladmin -u root create tenancy
echo "Recreated tenancy database"

if [ -f "vendor/laravel/laravel/config/tenancy.php" ]; then
    rm vendor/laravel/laravel/config/tenancy.php
    echo "Removed tenancy.php configuration file from the vendor folder"
fi
if [ -f "vendor/laravel/laravel/database/migrations/2017_01_01_000000_tenancy_customers.php" ]; then
    rm vendor/laravel/laravel/database/migrations/2017_01_01_000000_tenancy_customers.php
    echo "Removed migration file from the vendor folder"
fi

if [ -f "vendor/laravel/laravel/database/migrations/2017_01_01_000003_tenancy_websites.php" ]; then
    rm vendor/laravel/laravel/database/migrations/2017_01_01_000003_tenancy_websites.php
    echo "Removed migration file from the vendor folder"
fi

if [ -f "vendor/laravel/laravel/database/migrations/2017_01_01_000005_tenancy_hostnames.php" ]; then
    rm vendor/laravel/laravel/database/migrations/2017_01_01_000005_tenancy_hostnames.php
    echo "Removed migration file from the vendor folder"
fi

if [ -f "vendor/laravel/laravel/database/migrations/2018_04_06_000001_tenancy_websites_needs_db_host.php" ]; then
    rm vendor/laravel/laravel/database/migrations/2018_04_06_000001_tenancy_websites_needs_db_host.php
    echo "Removed migration file from the vendor folder"
fi
