# SPINEN's Discourse SSO for Laravel

[![Latest Stable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/stable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Latest Unstable Version](https://poser.pugx.org/spinen/laravel-discourse-sso/v/unstable)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![Total Downloads](https://poser.pugx.org/spinen/laravel-discourse-sso/downloads)](https://packagist.org/packages/spinen/laravel-discourse-sso)
[![License](https://poser.pugx.org/spinen/laravel-discourse-sso/license)](https://packagist.org/packages/spinen/laravel-discourse-sso)

[Discourse](https://www.discourse.org) is a great online forum software that supports Single Sign On ([SSO](https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045)).  There is a great PHP library that handles all of the heavy lifting to make the SSO work called [cviebrock/discourse-php](https://github.com/cviebrock/discourse-php), which this package uses.  This package is loosely based on the work done by [jaewun/discourse-sso-laravel](https://github.com/jaewun/discourse-sso-laravel).

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Develop | [![Build Status](https://github.com/spinen/laravel-discourse-sso/workflows/CI/badge.svg?branch=develop)](https://github.com/spinen/laravel-discourse-sso/workflows/CI/badge.svg?branch=develop) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=develop) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=develop) |
| Master | [![Build Status](https://github.com/spinen/laravel-discourse-sso/workflows/CI/badge.svg?branch=master)](https://github.com/spinen/laravel-discourse-sso/workflows/CI/badge.svg?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-discourse-sso/?branch=master) |

## Prerequisite

#### NOTE: If you need to use < PHP 7.2, please stay with version 1.x

Aside from Laravel >= 5.5, there is 1 package that is required.

* ["cviebrock/discourse-php": "^0.9.3",](https://github.com/cviebrock/discourse-php)

## Install

Install Discourse SSO for Laravel:

```bash
$ composer require spinen/laravel-discourse-sso
```

The package uses the [auto registration feature](https://laravel.com/docs/5.8/packages#package-discovery) of Laravel 5.

## Package Configuration

All of the configuration values are stored in under a `discourse` key in `config/services.php`.  Here is the array to add...

```php
    'discourse' => [
        // Middleware for the SSO login route to use
        'middleware' => ['web', 'auth'],
    
        // The route's URI that acts as the entry point for Discourse to start the SSO process.
        // Used by Discourse to route incoming logins.
        'route' => 'discourse/sso',
        
        // Secret string used to encrypt/decrypt SSO information,
        // be sure that it is 10 chars or longer
        'secret' => env('DISCOURSE_SECRET'),
        
        // Disable Discourse from sending welcome message
        'suppress_welcome_message' => 'true',
        
        // Where the Discourse forum lives
        'url' => env('DISCOURSE_URL'),
        
        // Api-specific items
        // For logging out of Discourse directly, generate an API key as an "All user key" and put the key & user here.
        // @see https://meta.discourse.org/t/how-to-create-an-api-key-on-the-admin-panel/87383
        'api' => [
            'key' => env('DISCOURSE_API_KEY'),
            'user' => env('DISCOURSE_API_USER'),
        ],

        // User-specific items
        // NOTE: The 'email' & 'external_id' are the only 2 required fields
        'user' => [
            // Check to see if the user has forum access & should be logged in via SSO
            'access' => null,
        
            // Discourse Groups to make sure that the user is part of in a comma-separated string
            // NOTE: Groups cannot have spaces in their names & must already exist in Discourse
            'add_groups' => null,

            // Boolean for making the user a Discourse admin. Leave null to ignore
            'admin' => null,

            // Full path to user's avatar image
            'avatar_url' => null,
            
            // The avatar is cached, so this triggers an update
            'avatar_force_update' => false,
            
            // Content of the user's bio
            'bio' => null,
            
            // Verified email address (see "require_activation" if not verified)
            'email' => 'email',
            
            // Unique string for the user that will never change
            'external_id' => 'id',
            
            // Boolean for making user a Discourse moderator. Leave null to ignore 
            'moderator' => null,
            
            // Full name on Discourse if the user is new or 
            // if SiteSetting.sso_overrides_name is set
            'name' => 'name',

            // Discourse Groups to make sure that the user is *NOT* part of in a comma-separated string.
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
     * Is the user a Discourse moderator?
     *
     * @param  string  $value
     * @return boolean
     */
    public function getDiscourseModeratorAttribute($value)
    {
        return ends_with($this->email, "yourdomain.tld");
    }
```

## Discourse Configuration
### Settings -> Login
These are the configs we have under `Settings -> Login`. If a setting isn't listed, then ours is set to the default value.

| **Setting**              | **Value**                                                |
|--------------------------|----------------------------------------------------------|
| login required           | true                                                     |
| enable sso               | true                                                     |
| sso url                  | Our Laravel's SSO route (FQDN)                           |
| sso secret               | Our SSO secret key                                       |
| sso overrides bio        | true                                                     |
| sso overrides email      | true                                                     |
| sso overrides username   | true                                                     |
| sso overrides name       | true                                                     |
| sso overrides avatar     | true                                                     |
| sso not approved url     | Our Laravel homepage (same as `url` in `config/app.php`) |
| hide email address taken | true                                                     |
_______________________________________________________________________


### Settings -> Users
These are the configs we have under `Settings -> Users`. If a setting isn't listed, then ours is set to the default value.

| **Setting**                               | **Value**                                                |
|-------------------------------------------|----------------------------------------------------------|
| reserved usernames                        | We added our client's company name                       |
| min password length                       | 8                                                        |
| min admin password length                 | 8                                                        |
| email editable                            | false                                                    |
| logout redirect                           | Our Laravel homepage (same as `url` in `config/app.php`) |
| purge unactivated users grace period days | 30                                                       |
| hide user profiles from public            | true                                                     |
_______________________________________________________________________

## Logging out the Discourse User

There's a listener in `src/Listeners/LogoutDiscourseUser.php` that will automatically log out the user from Discourse when certain events are fired. To use the Listener, [you need to register the event](https://laravel.com/docs/master/events#registering-events-and-listeners) in the `$listen` array in your `EventServiceProvider`.

When a Laravel `User` logs out, to log out their Discourse session Simply add the Laravel `Logout` event & the `LogoutDiscourseUser` listener in that `$listen` array. If you want to log out Discourse users on a Laravel `User` being deleted or disabled, make your own event class and register it the same way.

### Example

```php
    protected $listen = [
        \Illuminate\Auth\Events\Logout::class => [
            \Spinen\Discourse\Listeners\LogoutDiscourseUser::class,
        ],
        \App\Events\YourCustomEvent::class => [
            \Spinen\Discourse\Listeners\LogoutDiscourseUser::class,
        ],
    ];
```

## Left to do

* badges for user
* support for [`custom_fields`](https://meta.discourse.org/t/custom-user-fields-for-plugins/14956)
* failed login redirect
* `return_paths` support
