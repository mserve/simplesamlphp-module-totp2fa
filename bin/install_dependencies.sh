#!/usr/bin/env bash

set -e

# Install composer
if [ ! -x "bin/composer.phar" ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=bin/
fi

# Install dependencies (without vcs history or dev tools)
php "bin/composer.phar" install --no-dev --prefer-dist -o
