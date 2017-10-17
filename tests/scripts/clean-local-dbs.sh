#!/bin/bash

DATABASES="$(mysql -u root -Bse 'show databases')"
REGEX="^([a-z0-9]{8}\-)([a-z0-9]{4}\-){3}([a-z0-9]{12})"

for db in $DATABASES; do
        if [[ $db =~ $REGEX ]]; then
                echo "Deleting $db"
                echo Y | mysqladmin -u root drop $db
        fi

done

echo Y | mysqladmin -u root drop tenancy
mysqladmin -u root create tenancy
echo "Recreated tenancy database"
if [ -f "vendor/laravel/laravel/tenancy.json" ]; then
    rm vendor/laravel/laravel/tenancy.json
    echo "Removed tenancy.json installation file"
fi
