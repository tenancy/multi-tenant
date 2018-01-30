#!/bin/bash

DATABASES="$(mysql -u root -Bse 'show databases')"
REGEX="^([a-z0-9]{8}\-?)([a-z0-9]{4}\-?){3}([a-z0-9]{12})"

for db in $DATABASES; do
    if [[ $db =~ $REGEX ]]; then
        echo "Deleting database $db"
        echo Y | mysqladmin -u root drop $db
    fi
done

USERS="$(mysql -u root -Bse 'select concat(User, "@", Host) from mysql.user')"
for user in $USERS; do
    if [[ $user =~ $REGEX ]]; then
            echo "Deleting user $user"
            mysql -u root -Bse "drop user $user"
    fi
done

echo Y | mysqladmin -u root drop tenancy
mysqladmin -u root create tenancy
echo "Recreated tenancy database"

if [ -f "vendor/laravel/laravel/config/tenancy.php" ]; then
    rm vendor/laravel/laravel/config/tenancy.php
    echo "Removed tenancy.php configuration file from the vendor folder"
fi
