#!/bin/sh
# https://getcomposer.org/
EXPECTED_SIGNATURE=$(wget https://composer.github.io/installer.sig -O - -q)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" = "$ACTUAL_SIGNATURE" ]; then
    php composer-setup.php --quiet
    RESULT=$?
    rm composer-setup.php
    exit $RESULT
else
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

if [ -f "./composer.phar" ]; then
    chmod a+x ./composer.phar

    # Install support for bower and NPM packages
    ./composer.phar global require "fxp/composer-asset-plugin:~1.1"
fi
