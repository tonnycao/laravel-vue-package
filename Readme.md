Data Loader
===========

This Laravel package allows end users to manage the regular data loading jobs.

### Features

- System jobs

  Traditionally, we schedule a job per need basis on CRON tab. However, this package allows users to manage the system
  job schedule from the application interface. Thus making end user responsible to manage any configuration change as
  and when needed.

- Adhoc jobs

  Sometimes a user needs to run a job on adhoc basis because of an error or system unavailability. This package
  facilitates application user to create an adhoc job.

- Download attachment from email or [SFTP][1] or [Box][5]

  As many of our applications have data sent to a system email account, which needs to be downloaded for applications
  to process and parse the data. This package includes this functionality.

  For SFTP, please refer [below][1] necessary configuration before use.

- Data version tags

  A developer could add `tag` and `active` columns to the table where data is loaded and use the active flag to query
  active data. With this in place, a business user could self manage the active dataset in case of any issue arising
  due to a bad data source.

## Dependencies

This package depends on the following packages.

- [Laravel][2] version 5.6 or above
- [cb/fiscal][3] package for converting a date to Apple fiscal date
- [php-unit][4] for tests

### Views

Laravel views depend on the following packages:

- jQuery
- Bootstrap v4

## How to use

Follow the below procedure to first install the package.

### Install the package using composer.

Add the following to your require key.
```
"require": {
     "fdt/data-loader": "^1.3.1",
},
```

Add the following private repository.
```
"repositories": [
    {
        "type": "composer",
        "url": "https://packagist.apple.com"
    },
]
```

Install or update composer
```
composer update
```

### SFTP as source

To use SFTP as source, ensure you have `league/flysystem-sftp` driver configured to your application

```
composer require league/flysystem-sftp
```

###### Note: **Config assumes name sftp as disk**
_config/filesystems.php_

```php
<?php

'sftp' => [
    'driver' => 'sftp',
    'host' => 'example.com',
    'port' => 21,
    'username' => 'username',
    'password' => 'password',
    'privateKey' => 'path/to/or/contents/of/privatekey',
    'root' => '/path/to/root',
    'timeout' => 10,
]
```

Extend Laravel's filesystem with the new driver by adding the following code to the boot() method of _AppServiceProvider_ (or other appropriate service provider):

```php
<?php

use Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
...
public function boot()
{
    Storage::extend('sftp', function ($app, $config) {
        return new Filesystem(new SftpAdapter($config));
    });
}
```

### Box as source

To use box as source, ensure that you create the box application on [https://appledev.app.box.com/developers/console][7]

On box developer console, create an oAuth 2.0 based application. Then configure the `OAuth 2.0 Redirect URI`,
`Application Scopes` and `CORS Domains`.

#### Configure

Under `config/box.php` of your application, you can specify the parameters or use .env file to
configure the box application specific settings.

```php
<?php

return [
    'host_url' => env('BOX_HOST_URL', ''),
    'client_id' => env('BOX_CLIENT_ID', ''), // OAuth 2.0 Credentials
    'client_secret' => env('BOX_CLIENT_SECRET', ''), // OAuth 2.0 Credentials
    'redirect_url' => env('BOX_REDIRECT_URL', ''), // https://{APP_HOST}/box/callback
    'auth_url' => env('BOX_AUTH_URL', 'https://apple.app.box.com/api/oauth2/authorize'),
    'token_url' => env('BOX_TOKEN_URL', 'https://api.box.com/oauth2/token'),
    'owner_url' => env('BOX_OWNER_URL', 'https://api.box.com/2.0/users/me'),
    'api_version' => env('BOX_API_VERSION'),
    'endpoints' => env('BOX_ENDPOINTS'),
    'upload_endpoints' => env('BOX_UPLOAD_ENDPOINTS'),
    'app_root_folder' => env('BOX_APP_ROOT_FOLDER', ''),
    'app_landing_url' => env('BOX_APP_LANDING_PAGE', ''),
];
```

#### Authorize your App

Head to the box redirect URL i.e. `https://{APP_HOST}/box/callback` to authorize the Box App and generate your
access tokens. **This step is only required once or if the refresh token expires which is unlikely as a refresh token is
valid for 30 days.**

### Publish assets

Run the following command and choose option for `FDT\DataLoader\DataLoaderServiceProvider`

```
php artisan vendor:publish
```

After running the command, first run migrations.

```
php artisan migrate
```

At this point you have all the above functionality to begin with your application specific logic. The following
[documentation][6] will explain on how to customize it for your application.

[1]: #sftp-as-source
[2]: https://packagist.org/packages/laravel/framework
[3]: https://packagist.apple.com/packages/cb/fiscal
[4]: https://packagist.org/packages/phpunit/phpunit
[5]: #box-as-source
[6]: example/Readme.md
[7]: https://appledev.app.box.com/developers/console
