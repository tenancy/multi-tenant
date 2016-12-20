#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev

if [ -n "$TRAVIS_BUILD_DIR" ]; then
    CI_PROJECT_DIR=$TRAVIS_BUILD_DIR
fi

# set symlink so it seems as if this is a factual laravel installation
ln -s $CI_PROJECT_DIR/vendor/ $CI_PROJECT_DIR/vendor/laravel/laravel/vendor

# moves the unit test to the root laravel directory
cp ci.travis.xml phpunit.xml

cd $CI_PROJECT_DIR
