# SPINEN's Discourse SSO for Laravel

[![Latest Stable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/stable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Total Downloads](https://poser.pugx.org/spinen/laravel-discourse-sso/downloads)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Latest Unstable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/unstable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Dependency Status](https://gemnasium.com/spinen/laravel-discourse-sso.svg)](https://gemnasium.com/spinen/laravel-discourse-sso)
[![License](https://poser.pugx.org/spinen/laravel-discourse-sso/license)](https://packagist.org/packages/spinen/laravel-discourse-sso)

[Discourse](https://www.discourse.org) is a great online forum software that supports Single Sign On ([SSO](https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045)).  There is a great PHP library that handles all of the heavy lifting to make the SSO work called [cviebrock/discourse-php](https://github.com/cviebrock/discourse-php), which this package uses.  This package is loosely based on the work done by [jaewun/discourse-sso-laravel](https://github.com/jaewun/discourse-sso-laravel).

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Develop | [![Build Status](https://travis-ci.org/spinen/laravel-discourse-sso.svg?branch=develop)](https://travis-ci.org/spinen/laravel-discourse-sso) | [![Coverage Status](https://coveralls.io/repos/spinen/laravel-discourse-sso/badge.svg?branch=develop&service=github)](https://coveralls.io/github/spinen/laravel-discourse-sso?branch=develop) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=develop) |
| Master | [![Build Status](https://travis-ci.org/spinen/laravel-discourse-sso.svg?branch=master)](https://travis-ci.org/spinen/laravel-discourse-sso) | [![Coverage Status](https://coveralls.io/repos/spinen/laravel-discourse-sso/badge.svg?branch=master&service=github)](https://coveralls.io/github/spinen/laravel-discourse-sso?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=master) |

## Prerequisite

Aside from Laravel >= 5.2, there is 1 package that is required.  NOTE: There are limitations in the way that 5.1 deals with collections, so I am taking the easy way & just not supporting it.  If you need this in 5.1, then please make a merge request on the `SsoController`.

* ["cviebrock/discourse-php": "^0.9.3",](https://github.com/cviebrock/discourse-php)

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

```php
    'discourse' => [
        // The route's URI that acts as the entrypoint for Discourse to start the SSO process.
        // Used by Discourse to route incoming logins.
        'route' => 'discourse/sso',
        
        // Secret string used to encrypt/decrypt SSO information,
        // be sure that it is 10 chars or longer
        'secret' => env('DISCOURSE_SECRET'),
        
        // Disable Discourse from sending welcome message
        'suppress_welcome_message' => 'true',
        
        // Where the Discourse form lives
        'url' => env('DISCOURSE_URL'),
        
        // User specific items
        // NOTE: The 'email' & 'external_id' are the only 2 required fields
        'user' => [
            // Check to see if the user has forum access & should be logged in via SSO
            'access' => null,
        
            // Groups to make sure that the user is part of in a comma-separated string
            // NOTE: Groups cannot have spaces in their names & must already exist in Discourse
            'add_groups' => null,

            // Boolean for user a Discourse admin, leave null to ignore
            'admin' => null,

            // Full path to user's avatar image
            'avatar_url' => null,
            
            // The avatar is cached, so this triggers an update
            'avatar_force_update' => false,
            
            // Content of the user's bio
            'bio' => null,
            
            // Verified email address (see "require_activation" if not verified)
            'email' => 'email',
            
            // Unique string to the user that will never change
            'external_id' => 'id',
            
            // Boolean for user a Discourse admin, leave null to ignore 
            'moderator' => null,
            
            // Full name on Discourse if the user is new or 
            // if SiteSetting.sso_overrides_name is set
            'name' => 'name',

            // Groups to make sure that the user is *NOT* part of in a comma-separated string
            // NOTE: Groups cannot have spaces in their names & must already exist in Discourse
            // There is not a way to specify the exact list of groups that a user is in, so
            // you may want to send the inverse of the 'add_groups'
            'remove_groups' => null,
            
            // If the email has not been verified, set this to true
            'require_activation' => false,
            
            // username on Discourse if the user is new or 
            // if SiteSetting.sso_overrides_username is set
            'username' => 'email',
        ],
    ],
```

The value of the properties for the `user` property can be one of 4 values...

1. `false` -- passed as set to Discourse
2. `true` -- passed as set to Discourse
3. `null` -- disables sending property to Discourse
4. a `string` -- name of a property on the `User` model

You can then add logic to the `User` model inside of [Accessors](https://laravel.com/docs/master/eloquent-mutators#defining-an-accessor) to provide the values for the properties configured for the user.  For example, if you wanted any user with an email address that matched "yourdomain.tld" to be a moderator, then you could set the `moderator` property to a string like `discourse_moderator` and add the following to your `User` model...

```php
    /**
     * Is the user a Discourse moderator.
     *
     * @param  string  $value
     * @return boolean
     */
    public function getDiscourseModeratorAttribute($value)
    {
        return ends_with($this->email, "yourdomain.tld");
    }
```

## Left to do

* document Discourse configuration
* send `log out` to Discourse when disabling/deleting the user
* badges to user
* support for [`custom_fields`](https://meta.discourse.org/t/custom-user-fields-for-plugins/14956)
* failed login redirect
* `return_paths` support
