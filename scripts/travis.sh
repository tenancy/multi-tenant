#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev

# moves the unit test to the root laravel directory
cp phpunit.travis.xml phpunit.xml

phpunit --coverage-text --coverage-clover=coverage.clover