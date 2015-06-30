#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev

if [[ "${$TRAVIS_BRANCH}" =~ (([0-9]+\.)+[0-9]+) ]]
    composer require ${TRAVIS_REPO_SLUG} ${$TRAVIS_BRANCH}
else
    composer require ${TRAVIS_REPO_SLUG} dev-${$TRAVIS_BRANCH}
fi

# moves the unit test to the root laravel directory
cp ./vendor/hyn-me/multi-tenant/phpunit.travis.xml ./phpunit.xml

phpunit
