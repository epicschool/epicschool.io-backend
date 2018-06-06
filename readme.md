# Lumen microframework backend

>

## Build Setup

## Create the .env file

Setup the database,app urls and mailgun configuration in .env file

``` bash
APP_FRONT_END_BASE_URL=http://localhost:8080/
APP_ADMIN_EMAIL=admin@epicschool.io

DB_DATABASE=EpicDbName
DB_USERNAME=EpicUserName
DB_PASSWORD=EpicPassword

```

## installing composer packages and migrating the database 

``` bash

# install composer packages
composer install

# migrate the database
php artisan migrate

# seed the database tables
php artisan db:seed

# run php server
php -S localhost:8000 -t public

# to reset the database and seed all tables run :
php artisan migrate:refresh --seed

```

## Configs
``` bash
# set the API_BASE_URL in following files
.
├.env
├── config
|   ├── filesystem.php
|   └── mail.php
|   └── sentry.php
|   └── services.php
```

## composer packages
``` bash
    "require": {
        "php": ">=7.1.3",
        "laravel/lumen-framework": "5.6.*",
        "vlucas/phpdotenv": "~2.2",
        "illuminate/mail": "5.6.*",
        "nesbot/carbon": "^1.22",
        "guzzlehttp/guzzle": "^6.3",
        "barryvdh/laravel-cors": "^0.11.0",
        "league/flysystem": "^1.0",
        "intervention/image": "^2.4",
        "sentry/sentry-laravel": "^0.9.0"
    },
```

# Lumen PHP Framework

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).
