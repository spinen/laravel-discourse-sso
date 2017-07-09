# SPINEN's Discourse SSO for Laravel

[![Latest Stable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/stable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Total Downloads](https://poser.pugx.org/spinen/laravel-discourse-sso/downloads)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Latest Unstable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/unstable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Dependency Status](https://www.versioneye.com/php/spinen:laravel-discourse-sso/0.1.1/badge.svg)](https://www.versioneye.com/php/spinen:laravel-discourse-sso/0.1.1)
[![License](https://poser.pugx.org/spinen/laravel-discourse-sso/license)](https://packagist.org/packages/spinen/laravel-discourse-sso)

[Discourse](https://www.discourse.org) is a great online forum software that supports Single Sign On ([SSO](https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045)).  There is a great PHP library that handles all of the heavy lifting to make the SSO work called [cviebrock/discourse-php](https://github.com/cviebrock/discourse-php), which this package uses.  This package is loosely based on the work done by [jaewun/discourse-sso-laravel](https://github.com/jaewun/discourse-sso-laravel).

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Develop | [![Build Status](https://travis-ci.org/spinen/laravel-discourse-sso.svg?branch=develop)](https://travis-ci.org/spinen/laravel-discourse-sso) | [![Coverage Status](https://coveralls.io/repos/spinen/laravel-discourse-sso/badge.svg?branch=develop&service=github)](https://coveralls.io/github/spinen/laravel-discourse-sso?branch=develop) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=develop) |
| Master | [![Build Status](https://travis-ci.org/spinen/laravel-discourse-sso.svg?branch=master)](https://travis-ci.org/spinen/laravel-discourse-sso) | [![Coverage Status](https://coveralls.io/repos/spinen/laravel-discourse-sso/badge.svg?branch=master&service=github)](https://coveralls.io/github/spinen/laravel-discourse-sso?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=master) |

## Prerequisite

As side from Laravel >= 5.1, there is 1 package that is required.

* ["cviebrock/discourse-php": "^0.9.3",](https://github.com/briannesbitt/Carbon)

## Install

Install Discourse SSO for Laravel:

```bash
    $ composer require spinen/laravel-discourse-sso
```

### For >= Laravel 5.5, you are done with the Install

The package uses the auto registration feature

### For < Laravel 5.5, you have to register the Service Provider

Add the Service Provider to `config/app.php`:

```php
    'providers' => [
        // ...
        Spinen\Discourse\SsoServiceProvider::class,
    ];
```

## Configuration

All of the configuration values are stored in under a `discourse` key in `config/services.php`.  Here is the array to add...

TODO: Put all of the config stuff here

## Using the package

TODO: Fill this in
