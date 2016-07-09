Fake Reservation System
=======================

Allows you to send mails to your Google Inbox that trigger Google Now cards.

To set up a server for yourself, you have to get an API key from
https://console.developers.google.com/apis/, download the `client_secret.json`
and put it into the root directory.


Installation
============

After checkout, download Composer using the `composer-dl.sh`. If you decided to
get Composer manually, you have to also enable the [fxp-asset](https://packagist.org/packages/fxp/composer-asset-plugin)
plugin:

    $ ./composer.phar global require "fxp/composer-asset-plugin:~1.1"

After that, install all dependencies by running:

    $ ./composer.phar install

Finally, get an API key from the [Google Developer Console](https://console.developers.google.com/apis/)
and download your `client_secret.json` into the root directory of this project.
