#!/usr/bin/env bash

set -e

VERSION=$1
REPOPATH=$2

if ! shift; then
    echo "$0: Missing required version parameter." >&2
    exit 1
fi

if [ -z "$VERSION" ]; then
    echo "$0: Empty version parameter." >&2
    exit 1
fi

if [ -z "$REPOPATH" ]; then
    REPOPATH="https://github.com/mserve/simplesamlphp-module-totp2fa.git"
fi

TAG="v$VERSION"
TARGET="simplesamlphp-module-totp2fa-$VERSION"

cd /tmp

if [ -a "$TARGET" ]; then
    echo "$0: Destination already exists: $TARGET" >&2
    exit 1
fi

umask 0022

git clone $REPOPATH $TARGET
cd $TARGET
git checkout $TAG
cd ..

if [ ! -x "$TARGET/composer.phar" ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=$TARGET
fi

# Set the version in composer.json
php "$TARGET/composer.phar" config version "v$VERSION" -d "$TARGET"

# Install dependencies (without vcs history or dev tools)
php "$TARGET/composer.phar" install --no-dev --prefer-dist -o -d "$TARGET"


rm -rf "$TARGET/.git"
rm -rf "$TARGET/bin"
rm "$TARGET"/.gitignore
rm "$TARGET/composer.phar"
tar --owner 0 --group 0 -cvzf "$TARGET.tar.gz" "$TARGET"
rm -rf "$TARGET"

echo "Created: /tmp/$TARGET.tar.gz"
