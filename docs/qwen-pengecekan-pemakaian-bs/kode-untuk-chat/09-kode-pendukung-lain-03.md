# Kode existing: kode-pendukung-lain, bagian 3

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: config/app.php

SHA-256: `51c49a728d4b65476bbc14f1dac64efa04da01453e83db5687fa00d1a887ac52`

~~~~php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    // Keep stored timestamps compatible with existing UTC values.
    // Business dates and displayed action times follow this local timezone.
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];

~~~~

## FILE: config/auth.php

SHA-256: `c7e204e9785c9f596d66fb884b493f658f2327f157646bdb4088efd6b3a7773f`

~~~~php
<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

~~~~

## FILE: config/cache.php

SHA-256: `b8bcf382283330b94c0a602ff06452de819041ea709e28eea5065d51d1d7d085`

~~~~php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "octane",
    |                    "failover", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

];

~~~~

## FILE: config/database.php

SHA-256: `06759b7b36b8641e9025fe03dd728970c4cb02d5b58ff709cbb0649987b4bfc2`

~~~~php
<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];

~~~~

## FILE: config/filesystems.php

SHA-256: `dac3d2a0e7076987a26c58b53fa462f75b8956c43b3680257381b560ac990bdb`

~~~~php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'reimbursement_proofs' => [
            'driver' => 'local',
            'root' => storage_path('app/private/reimbursement-proofs'),
            'visibility' => 'private',
            'serve' => false,
            'throw' => true,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

~~~~

## FILE: config/logging.php

SHA-256: `299ec1ba5b5a803c8e20f666ae7628e442e856cc2ff7ac9e2adb17b97e8ad63b`

~~~~php
<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];

~~~~

## FILE: config/mail.php

SHA-256: `c43ff49a31c5f32ce21eb7159f2a4b3cd2f4074eaed5838d265646dbfedd3474`

~~~~php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];

~~~~

## FILE: config/queue.php

SHA-256: `6101774da7c8b79af46d0028f2b8b31a484c3f7eda10b76e5b27b6093ad364a4`

~~~~php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis",
    |          "deferred", "background", "failover", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];

~~~~

## FILE: config/sanctum.php

SHA-256: `f8ae35944312702e9004765c753e16ec8b0cb0a7bcc3f379efa250bfd7a53753`

~~~~php
<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. This will override any values set in the token's
    | "expires_at" attribute, but first-party sessions are not affected.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];

~~~~

## FILE: config/services.php

SHA-256: `8258c4487eb1d73a97e569eb0e411e3f25368044812df4cba807860432be1d92`

~~~~php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];

~~~~

## FILE: config/session.php

SHA-256: `f2e75e189021597e29940c0859c00c3088ec591e403768fff5143c5687a80f59`

~~~~php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that is utilized for
    | incoming requests. Laravel supports a variety of storage options to
    | persist session data. Database storage is a great default choice.
    |
    | Supported: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to expire immediately when the browser is closed then you may
    | indicate that via the expire_on_close configuration option.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your session data
    | should be encrypted before it's stored. All encryption is performed
    | automatically by Laravel and you may use the session like normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When utilizing the "file" session driver, the session files are placed
    | on disk. The default storage location is defined here; however, you
    | are free to provide another location where they should be stored.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table to
    | be used to store sessions. Of course, a sensible default is defined
    | for you; however, you're welcome to change this to another table.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | When using one of the framework's cache driven session backends, you may
    | define the cache store which should be used to store the session data
    | between requests. This must match one of your defined cache stores.
    |
    | Affects: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that it will
    | happen on a given request. By default, the odds are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Here you may change the name of the session cookie that is created by
    | the framework. Typically, you should not need to change this value
    | since doing so does not grant a meaningful security improvement.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as available. Typically, this will be the root path of
    | your application, but you're free to change this when necessary.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | This value determines the domain and subdomains the session cookie is
    | available to. By default, the cookie will be available to the root
    | domain without subdomains. Typically, this shouldn't be changed.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | By setting this option to true, session cookies will only be sent back
    | to the server if the browser has a HTTPS connection. This will keep
    | the cookie from being sent to you when it can't be done securely.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will prevent JavaScript from accessing the
    | value of the cookie and the cookie will only be accessible through
    | the HTTP protocol. It's unlikely you should disable this option.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this value to "lax" to permit secure cross-site requests.
    |
    | See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will tie the cookie to the top-level site for
    | a cross-site context. Partitioned cookies are accepted by the browser
    | when flagged "secure" and the Same-Site attribute is set to "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];

~~~~

## FILE: package.json

SHA-256: `e1bf666b49a401c06ede562b32fc741b8e8dc45cca0a67de136edfbd51335f33`

~~~~json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "axios": "^1.11.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^2.0.0",
        "tailwindcss": "^4.0.0",
        "vite": "^7.0.7"
    }
}

~~~~

## FILE: public/index.php

SHA-256: `eba77cba39695b6bd091fe5211d481f7ebb2ce2d8d26230b5a609465d0a4aff9`

~~~~php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

~~~~

## FILE: resources/css/app.css

SHA-256: `9f49ed837fdc773b89a294d1e86dc68828ef2f774d7e797ba5f89c2a08c84760`

~~~~css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
}

~~~~

## FILE: resources/js/app.js

SHA-256: `cf6dab196c721f6911ff99cc4f910897f978599a2d289971a316a5fc33091f94`

~~~~javascript
import './bootstrap';

~~~~

## FILE: resources/js/bootstrap.js

SHA-256: `6fd9fc82e68fa915a882f4e227b9faacbe7bfd66cb953c628840e83bda6b72f4`

~~~~javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

~~~~

## FILE: resources/views/Pagination/bootstrap-5-htmx.blade.php

SHA-256: `1d7437bd4dadb2458057e60813281633ac96a406ca4307a231afc89c694ee49c`

~~~~php
@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">« Sebelumnya</span></li>
            @else
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->previousPageUrl() }}"
                       hx-get="{{ $paginator->previousPageUrl() }}"
                       hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                       hx-swap="innerHTML"
                       hx-push-url="true"
                       rel="prev">« Sebelumnya</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link"
                                   href="{{ $url }}"
                                   hx-get="{{ $url }}"
                                   hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                                   hx-swap="innerHTML"
                                   hx-push-url="true">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->nextPageUrl() }}"
                       hx-get="{{ $paginator->nextPageUrl() }}"
                       hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                       hx-swap="innerHTML"
                       hx-push-url="true"
                       rel="next">Berikutnya »</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">Berikutnya »</span></li>
            @endif
        </ul>
    </nav>
@endif
~~~~

## FILE: resources/views/Positions/create.blade.php

SHA-256: `d37303a7443912db9fb60c98d813920f67dbe12d2b91d9f3c723cd9566885ee6`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Position</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Position</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('positions.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Position Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: OFFICE, PRODUKSI, MD, MT">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Position / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: Production, Office">{{ old('nama') }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Position</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Position</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Position</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $position)
                            <tr>
                                <td>{{ $position->id }}</td>
                                <td>{{ $position->code }}</td>
                                <td>{{ $position->nama }}</td>
                                <td>
                                    <span class="badge {{ $position->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $position->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('positions.edit', $position->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('positions.destroy', $position->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus position ini?')">
                                    Hapus
                                </button>
                            </form>
                                </td> 
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/Positions/edit.blade.php

SHA-256: `7869db5dd045872ed28bd64b92eb7448d40948decfbf73f78d10df4d3869f617`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Position</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Position</h4>
            </div>
            <div class="card-body">

    {{-- Tambahkan ini biar error validasi kelihatan --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('positions.update', $positions->id) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="mb-3">
            <label for="code" class="form-label">Position Code</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ $positions->code }}" readonly>
        </div>

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Position / Deskripsi</label>
            <textarea name="nama" id="nama" rows="2"
                class="form-control @error('nama') is-invalid @enderror"
                required>{{ old('nama', $positions->nama) }}</textarea>
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $positions->is_active ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('positions.create') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Update Master Position</button>
        </div>
    </form>
</div>
        </div>
    </div>
</body>
</html>


~~~~

## FILE: resources/views/approval-atasan/detail.blade.php

SHA-256: `2ae282f442eec3b799dd9eaec5cd78f0bfee68d676c7ec7406d4b9feba5fb16f`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body: #f8fafc; --bg-card: #ffffff; --text-primary: #0f172a; --text-secondary: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }
        .page-header { padding: 2rem 0 1rem; }
        .card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
        .section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem; }
        .info-row { display: flex; justify-content: space-between; gap: 1rem; padding: 0.6rem 0; border-bottom: 1px dashed var(--border-color); font-size: 0.92rem; }
        .info-row:last-child { border-bottom: 0; }
        .info-label { color: var(--text-secondary); font-weight: 600; flex-shrink: 0; }
        .info-value { font-weight: 600; text-align: right; }
        .nominal-big { font-size: 1.5rem; font-weight: 800; }
        .badge-status { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .member-chip { display: inline-flex; align-items: center; gap: 0.4rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; border-radius: 999px; padding: 0.35rem 0.8rem; font-size: 0.8rem; font-weight: 600; margin: 0 0.4rem 0.4rem 0; }

        /* 🎯 BARU: Badge Tipe */
        .badge-tipe {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.4em 0.85em; border-radius: 6px;
            font-weight: 700; font-size: 0.72rem;
            text-transform: uppercase; letter-spacing: 0.04em;
        }
        .badge-tipe-operasional { background: #eff6ff; color: #1d4ed8; }
        .badge-tipe-dinas       { background: #ecfdf5; color: #047857; }

        /* 🎯 BARU: Info box konteks anggota grup */
        .group-info-box {
            border-radius: 10px; padding: 0.85rem 1rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 0.88rem;
        }
        .group-info-box.solo { background: #f1f5f9; color: #475569; border: 1px dashed #cbd5e1; }
        .group-info-box.team { background: linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%); color: #3730a3; border: 1px solid #c7d2fe; }
        .group-info-box i.box-icon { font-size: 1.2rem; }

        .btn-act { border: none; padding: 0.6rem 1.1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; color: white; cursor: pointer; }
        .btn-approve { background-color: #10b981; }
        .btn-reject { background-color: #ef4444; }

        .timeline { position: relative; padding-left: 1.75rem; }
        .timeline::before { content: ''; position: absolute; left: 7px; top: 6px; bottom: 6px; width: 2px; background: var(--border-color); }
        .timeline-item { position: relative; padding-bottom: 1.25rem; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-dot { position: absolute; left: -1.75rem; top: 4px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #3b82f6; }
        .timeline-time { font-size: 0.75rem; color: var(--text-secondary); }
        .timeline-title { font-weight: 700; font-size: 0.9rem; }
        .timeline-desc { font-size: 0.85rem; color: var(--text-secondary); }
    </style>
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Detail Pengajuan BS</h3>
            <a href="{{ route('approval-atasan.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">❌ {{ session('error') }}</div>
        @endif

        @php
            // 🎯 Ambil tipe sekali, fallback untuk data lama
            $tipe = $bs->tipe_pengajuan ?? 'operasional_sales';
            $isPerjalanan = ($tipe === 'perjalanan_dinas');
            $tipeLabel = $isPerjalanan ? 'Perjalanan Dinas' : 'Operasional Sales';
        @endphp

        {{-- ================= BANNER STATUS KONTEKSTUAL ================= --}}
        @if($bs->status === 'PENDING_ATASAN')
            <div class="alert alert-warning border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-hourglass-split me-2"></i>
                Pengajuan <strong>{{ $tipeLabel }}</strong> ini menunggu persetujuan Anda.
            </div>
        @elseif($bs->status === 'APPROVED_ATASAN')
            <div class="alert alert-info border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-check-circle me-2"></i> Sudah Anda setujui — menunggu proses pencairan oleh Finance.
            </div>
        @elseif($bs->status === 'DISBURSED')
            <div class="alert alert-success border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-cash-coin me-2"></i> Sudah Anda setujui dan telah dicairkan oleh Finance.
            </div>
        @elseif($bs->status === 'SETTLED')
            <div class="alert alert-secondary border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-lock-fill me-2"></i> Pengajuan ini sudah selesai dan periodenya telah ditutup (SETTLED).
            </div>
        @elseif($bs->status === 'REJECTED_ATASAN')
            <div class="alert alert-danger border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-x-circle me-2"></i> Anda menolak pengajuan ini. Alasan: {{ $bs->rejection_reason }}
            </div>
        @elseif($bs->status === 'REJECTED_FINANCE')
            <div class="alert alert-danger border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-exclamation-triangle me-2"></i> Anda menyetujui, namun Finance menolak pencairan. Alasan: {{ $bs->rejection_reason }}
            </div>
        @endif

        {{-- ================= INFORMASI PENGAJUAN ================= --}}
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div class="section-title mb-0">Informasi Pengajuan</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- 🎯 BARU: Badge Tipe --}}
                    <span class="badge-tipe {{ $isPerjalanan ? 'badge-tipe-dinas' : 'badge-tipe-operasional' }}"
                          title="{{ $isPerjalanan ? 'Perjalanan Dinas bersama tim/grup' : 'Biaya operasional sales perorangan' }}">
                        <i class="bi {{ $isPerjalanan ? 'bi-airplane-fill' : 'bi-briefcase-fill' }}"></i>
                        {{ $tipeLabel }}
                    </span>

                    @php
                        $colors = [
                            'PENDING_ATASAN'   => 'bg-warning text-dark',
                            'APPROVED_ATASAN'  => 'bg-info text-white',
                            'REJECTED_ATASAN'  => 'bg-danger text-white',
                            'DISBURSED'        => 'bg-success text-white',
                            'REJECTED_FINANCE' => 'bg-danger text-white',
                            'SETTLED'          => 'bg-secondary text-white',
                        ];
                    @endphp
                    <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">{{ str_replace('_', ' ', $bs->status) }}</span>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">No. Dokumen</span>
                <span class="info-value">{{ $bs->document_no }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pengajuan</span>
                <span class="info-value">{{ \App\Support\AppTime::format($bs->created_at) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Periode</span>
                <span class="info-value">{{ $bs->periode }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Diajukan Oleh</span>
                <span class="info-value">{{ $bs->user?->nama ?? '-' }} <span class="text-muted fw-normal">(NIK: {{ $bs->user?->NIK ?? '-' }})</span></span>
            </div>
            <div class="info-row">
                <span class="info-label">Sisa Saldo BS (Snapshot)</span>
                <span class="info-value">Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</span>
            </div>
            <div class="info-row align-items-center">
                <span class="info-label">Nominal Pengajuan</span>
                <span class="info-value nominal-big">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- ================= DESKRIPSI ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">Deskripsi / Keperluan</div>
            <p class="mb-0" style="font-size: 0.92rem;">{{ $bs->description ?? 'Tidak ada deskripsi.' }}</p>
        </div>

        {{-- ================= ANGGOTA GRUP DINAS (dengan konteks cerdas) ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">
                {{ $isPerjalanan ? 'Anggota Grup Dinas' : 'Keanggotaan' }}
            </div>

            @if($isPerjalanan && $bs->members && $bs->members->count() > 0)
                {{-- 🎯 Perjalanan Dinas dengan tim — tampilkan chips anggota --}}
                <div class="group-info-box team mb-3">
                    <i class="bi bi-people-fill box-icon"></i>
                    <div>
                        <div class="fw-bold small">Perjalanan dinas bersama {{ $bs->members->count() }} anggota tim</div>
                        <div class="small opacity-75">Anggota yang tercantum berhak menggunakan BS ini untuk realisasi biaya di lapangan.</div>
                    </div>
                </div>
                <div>
                    @foreach($bs->members as $m)
                        <span class="member-chip"><i class="bi bi-person-check"></i> {{ $m->nama }}</span>
                    @endforeach
                </div>

            @elseif($isPerjalanan)
                {{-- 🎯 Perjalanan Dinas TAPI tanpa anggota (edge case) --}}
                <div class="group-info-box solo">
                    <i class="bi bi-person box-icon"></i>
                    <div>
                        <strong>Perjalanan Dinas — Perorangan</strong>
                        <div class="small opacity-75">Pengajuan tipe perjalanan dinas, namun tidak ada anggota tim yang dicantumkan.</div>
                    </div>
                </div>

            @else
                {{-- 🎯 Operasional Sales — selalu perorangan --}}
                <div class="group-info-box solo">
                    <i class="bi bi-person-workspace box-icon"></i>
                    <div>
                        <strong>Operasional Sales — Perorangan</strong>
                        <div class="small opacity-75">Tipe pengajuan ini hanya untuk pemohon sendiri (tidak melibatkan tim).</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ================= AUDIT TRAIL ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">Riwayat Proses</div>
            <div class="timeline">
                @forelse($logs as $log)
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div class="timeline-time">{{ \App\Support\AppTime::format($log->created_at) }} — oleh {{ $log->actor?->nama ?? 'System' }}</div>
                        <div class="timeline-title">{{ $log->activity_name ?? '-' }}</div>
                        <div class="timeline-desc">{{ $log->description ?? '-' }}</div>
                    </div>
                @empty
                    <span class="text-muted small">Belum ada riwayat.</span>
                @endforelse
            </div>
        </div>

        {{-- ================= KEPUTUSAN (hanya saat PENDING) ================= --}}
        @if($bs->status === 'PENDING_ATASAN')
            <div class="card-custom">
                <div class="section-title">Keputusan Anda</div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn-act btn-approve flex-fill btn-open-modal"
                            style="min-width: 200px;"
                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                            data-mode="approve"
                            data-action="{{ route('approval-atasan.approve', $bs->id) }}"
                            data-docno="{{ $bs->document_no }}">
                        <i class="bi bi-check-lg me-1"></i> Setujui & Teruskan ke Finance
                    </button>
                    <button type="button" class="btn-act btn-reject flex-fill btn-open-modal"
                            style="min-width: 200px;"
                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                            data-mode="reject"
                            data-action="{{ route('approval-atasan.reject', $bs->id) }}"
                            data-docno="{{ $bs->document_no }}">
                        <i class="bi bi-x-lg me-1"></i> Tolak Pengajuan
                    </button>
                </div>
            </div>

            <div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form id="decisionForm" method="POST">
                        @csrf
                        <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border-color);">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h5 class="modal-title fw-bold" id="decisionTitle">-</h5>
                                    <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem;">
                                        Dokumen: <span id="decisionDocNo" class="fw-semibold text-primary">-</span>
                                    </p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-secondary mb-3" style="font-size: 0.9rem;" id="decisionSubtitle"></p>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;" id="decisionLabel">-</label>
                                    <textarea class="form-control" id="decisionMessage" name="message"
                                              rows="4" maxlength="1000" placeholder=""></textarea>
                                    <small class="text-muted"><span id="charCount">0</span> / 1000</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn" id="decisionSubmitBtn">Konfirmasi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modal      = document.getElementById('decisionModal');
                    const form       = document.getElementById('decisionForm');
                    const title      = document.getElementById('decisionTitle');
                    const subtitle   = document.getElementById('decisionSubtitle');
                    const docNoEl    = document.getElementById('decisionDocNo');
                    const label      = document.getElementById('decisionLabel');
                    const message    = document.getElementById('decisionMessage');
                    const charCount  = document.getElementById('charCount');
                    const submitBtn  = document.getElementById('decisionSubmitBtn');

                    modal.addEventListener('show.bs.modal', function (event) {
                        const btn    = event.relatedTarget;
                        const mode   = btn.dataset.mode;
                        const action = btn.dataset.action;
                        const docno  = btn.dataset.docno;

                        docNoEl.textContent   = docno;
                        form.action           = action;
                        message.value         = '';
                        charCount.textContent = '0';

                        if (mode === 'approve') {
                            title.textContent    = 'Setujui Pengajuan';
                            subtitle.textContent = 'Tambahkan pesan atau catatan untuk pemohon (opsional).';
                            label.innerHTML      = 'Pesan Persetujuan <span class="text-muted fw-normal small">(Opsional)</span>';
                            message.placeholder  = 'Contoh: OK, segera cairkan di Finance...';
                            message.name         = 'approval_message';
                            message.required     = false;
                            submitBtn.className  = 'btn btn-success';
                            submitBtn.innerHTML  = '<i class="bi bi-check-lg me-1"></i> Ya, Setujui';
                        } else {
                            title.textContent    = 'Tolak Pengajuan';
                            subtitle.textContent = 'Mohon berikan alasan penolakan agar pemohon dapat memperbaikinya.';
                            label.innerHTML      = 'Alasan Penolakan <span class="text-danger">*</span>';
                            message.placeholder  = 'Tuliskan alasan penolakan...';
                            message.name         = 'rejection_reason';
                            message.required     = true;
                            submitBtn.className  = 'btn btn-danger';
                            submitBtn.innerHTML  = '<i class="bi bi-x-lg me-1"></i> Ya, Tolak';
                        }
                    });

                    message.addEventListener('input', function () {
                        charCount.textContent = this.value.length;
                    });
                });
            </script>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/approval-atasan/index.blade.php

SHA-256: `a95f98632ccc9b2e04ec0d757c44fbadc2e583ec265b5f6b31ee9eda96391b6a`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval BS Atasan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }

        .page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-weight: 800; font-size: 1.6rem; letter-spacing: -0.02em; margin: 0; }
        .page-subtitle { color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0 0; }
        .btn-nav {
            background-color: white; border: 1px solid var(--border-color);
            color: var(--text-secondary); font-weight: 500; font-size: 0.875rem;
            padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; white-space: nowrap;
        }
        .btn-nav:hover { background-color: #f1f5f9; color: var(--text-primary); }

        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 14px; padding: 1rem 1.25rem;
            display: flex; align-items: center; gap: 0.9rem; height: 100%;
            transition: all 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px -6px rgba(15,23,42,0.08); }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
        .stat-label { font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.15rem; }
        .stat-value { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; line-height: 1.2; }

        .toolbar { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 0.9rem 1.25rem; margin: 1rem 0; }
        .toolbar .form-select { border: 1.5px solid var(--border-color); border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; }
        .toolbar .form-select:focus { border-color: var(--text-primary); box-shadow: 0 0 0 3px rgba(15,23,42,0.08); }
        .current-badge { display: inline-block; background: #10b981; color: white; font-size: 0.65rem; font-weight: 700; padding: 0.25em 0.7em; border-radius: 999px; margin-left: 0.5rem; vertical-align: middle; }

        .card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom th { background-color: #f8fafc; color: var(--text-secondary); font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border-color); padding: 0.9rem 1rem; white-space: nowrap; }
        .table-custom td { border-bottom: 1px solid var(--border-color); padding: 0.85rem 1rem; font-size: 0.92rem; vertical-align: middle; }
        .table-custom tbody tr { transition: background 0.15s ease; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }
        .table-custom tbody tr:last-child td { border-bottom: none; }

        .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .avatar { width: 30px; height: 30px; border-radius: 50%; background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .badge-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38em 0.75em; border-radius: 999px; font-weight: 700; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: 0.9; }

        /* 🎯 BARU: Badge Tipe Pengajuan */
        .badge-tipe {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.35em 0.7em; border-radius: 999px;
            font-weight: 700; font-size: 0.68rem;
            text-transform: uppercase; letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .badge-tipe-operasional { background: #eff6ff; color: #1d4ed8; }
        .badge-tipe-dinas       { background: #ecfdf5; color: #047857; }

        .btn-act { border: none; padding: 0.42rem 0.75rem; border-radius: 8px; font-weight: 600; font-size: 0.78rem; color: white; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; transition: all 0.15s ease; }
        .btn-act:hover { transform: translateY(-1px); filter: brightness(1.08); color: white; }
        .btn-approve { background-color: #10b981; }
        .btn-reject  { background-color: #ef4444; }
        .btn-view    { background-color: #3b82f6; }
    </style>
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1300px;">

        <div class="page-header">
            <div>
                <h1 class="page-title">Approval BS Atasan</h1>
                <p class="page-subtitle">Setujui atau tolak pengajuan Bon Sementara dari tim Anda.</p>
            </div>
            <a href="{{ route('dashboard.transaksi') }}" class="btn-nav">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 12px;">❌ {{ session('error') }}</div>
        @endif

        @php
            $bulanId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $formatPeriode = fn($p) => ($bulanId[(int)substr($p,5,2)] ?? '-') . ' ' . substr($p,0,4);
            $currentPeriod = \App\Support\AppTime::now()->format('Y-m');

            $menunggu      = $data->where('status', 'PENDING_ATASAN')->count();
            $disetujui     = $data->whereIn('status', ['APPROVED_ATASAN', 'DISBURSED', 'SETTLED'])->count();
            $ditolak       = $data->whereIn('status', ['REJECTED_ATASAN', 'REJECTED_FINANCE'])->count();
            $totalDiajukan = $data->sum('nominal_pengajuan');
        @endphp

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                    <div><div class="stat-label">Menunggu</div><div class="stat-value" style="color:#d97706;">{{ $menunggu }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-check2-circle"></i></div>
                    <div><div class="stat-label">Disetujui</div><div class="stat-value" style="color:#059669;">{{ $disetujui }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-x-octagon"></i></div>
                    <div><div class="stat-label">Ditolak</div><div class="stat-value" style="color:#dc2626;">{{ $ditolak }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-cash-stack"></i></div>
                    <div><div class="stat-label">Total Diajukan</div><div class="stat-value money" style="font-size:1.1rem;">Rp {{ number_format($totalDiajukan, 0, ',', '.') }}</div></div>
                </div>
            </div>
        </div>

        <div class="toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-0" style="font-size:1.1rem;">
                    {{ $formatPeriode($selectedPeriod) }}
                    @if($selectedPeriod === $currentPeriod)
                        <span class="current-badge">BULAN INI</span>
                    @endif
                </h4>
                <p class="text-muted small mb-0">
                    Menampilkan <strong>{{ $data->count() }}</strong> pengajuan pada periode ini.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="periodSelect" class="small fw-semibold text-muted mb-0 text-nowrap">
                    <i class="bi bi-calendar3 me-1"></i>Periode:
                </label>
                <select id="periodSelect" class="form-select form-select-sm" style="min-width:210px;">
                    @foreach($availablePeriods as $p)
                        <option value="{{ $p }}" {{ $selectedPeriod === $p ? 'selected' : '' }}>
                            {{ $formatPeriode($p) }}{{ $p === $currentPeriod ? ' (Bulan ini)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Doc No</th>
                            <th>Tipe</th> {{-- 🎯 BARU --}}
                            <th>Tgl Pengajuan</th>
                            <th>Tgl Keputusan</th>
                            <th>User</th>
                            <th>Nominal</th>
                            <th>Sisa BS (Snap)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $bs)
                        @php
                            // 🎯 BARU: Tentukan tipe (fallback 'operasional_sales' untuk data lama)
                            $tipe      = $bs->tipe_pengajuan ?? 'operasional_sales';
                            $isPerjalanan = ($tipe === 'perjalanan_dinas');
                        @endphp
                        <tr>
                            <td class="fw-semibold text-nowrap">{{ $bs->document_no }}</td>

                            {{-- 🎯 BARU: Kolom Tipe Pengajuan --}}
                            <td>
                                @if($isPerjalanan)
                                    <span class="badge-tipe badge-tipe-dinas" title="Perjalanan Dinas dengan tim/grup">
                                        <i class="bi bi-airplane-fill"></i> Perjalanan Dinas
                                    </span>
                                @else
                                    <span class="badge-tipe badge-tipe-operasional" title="Operasional Sales perorangan">
                                        <i class="bi bi-briefcase-fill"></i> Operasional Sales
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">{{ \App\Support\AppTime::format($bs->created_at) }}</td>
                            <td class="text-nowrap">
                                @if($decision = $bs->logs->first())
                                    {{ \App\Support\AppTime::format($decision->created_at) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($bs->user->nama ?? '?', 0, 1)) }}</span>
                                    {{ $bs->user->nama ?? '-' }}
                                </div>
                            </td>
                            <td class="money fw-bold text-primary">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</td>
                            <td class="money text-muted">Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $colors = [
                                        'PENDING_ATASAN'   => 'bg-warning text-dark',
                                        'APPROVED_ATASAN'  => 'bg-info text-white',
                                        'REJECTED_ATASAN'  => 'bg-danger text-white',
                                        'DISBURSED'        => 'bg-success text-white',
                                        'REJECTED_FINANCE' => 'bg-danger text-white',
                                        'SETTLED'          => 'bg-secondary text-white',
                                    ];
                                @endphp
                                <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">
                                    {{ str_replace('_', ' ', $bs->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end align-items-center flex-nowrap">
                                    <a href="{{ route('approval-atasan.detail', $bs->id) }}" class="btn-act btn-view" title="Lihat Detail Pengajuan">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @if($bs->status == 'PENDING_ATASAN')
                                        <button type="button" class="btn-act btn-approve"
                                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                                            data-mode="approve"
                                            data-action="{{ route('approval-atasan.approve', $bs->id) }}"
                                            data-docno="{{ $bs->document_no }}">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button type="button" class="btn-act btn-reject"
                                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                                            data-mode="reject"
                                            data-action="{{ route('approval-atasan.reject', $bs->id) }}"
                                            data-docno="{{ $bs->document_no }}">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5"> {{-- 🎯 colspan 8 → 9 --}}
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada pengajuan pada <strong>{{ $formatPeriode($selectedPeriod) }}</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal SHARED (tidak ada perubahan) --}}
    <div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="decisionForm" method="POST">
                @csrf
                <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border-color);">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="decisionTitle">-</h5>
                            <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem;">
                                Dokumen: <span id="decisionDocNo" class="fw-semibold text-primary">-</span>
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary mb-3" style="font-size: 0.9rem;" id="decisionSubtitle"></p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;" id="decisionLabel">-</label>
                            <textarea class="form-control" id="decisionMessage" name="message"
                                      rows="4" maxlength="1000" placeholder=""></textarea>
                            <small class="text-muted"><span id="charCount">0</span> / 1000</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" id="decisionSubmitBtn">Konfirmasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('periodSelect')?.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('periode', this.value);
            window.location.href = url.toString();
        });

        document.addEventListener('DOMContentLoaded', function () {
            const modal      = document.getElementById('decisionModal');
            const form       = document.getElementById('decisionForm');
            const title      = document.getElementById('decisionTitle');
            const subtitle   = document.getElementById('decisionSubtitle');
            const docNoEl    = document.getElementById('decisionDocNo');
            const label      = document.getElementById('decisionLabel');
            const message    = document.getElementById('decisionMessage');
            const charCount  = document.getElementById('charCount');
            const submitBtn  = document.getElementById('decisionSubmitBtn');

            modal.addEventListener('show.bs.modal', function (event) {
                const btn    = event.relatedTarget;
                const mode   = btn.dataset.mode;
                const action = btn.dataset.action;
                const docno  = btn.dataset.docno;

                docNoEl.textContent   = docno;
                form.action           = action;
                message.value         = '';
                charCount.textContent = '0';

                if (mode === 'approve') {
                    title.textContent    = 'Setujui Pengajuan';
                    subtitle.textContent = 'Tambahkan pesan atau catatan untuk pemohon (opsional).';
                    label.innerHTML      = 'Pesan Persetujuan <span class="text-muted fw-normal small">(Opsional)</span>';
                    message.placeholder  = 'Contoh: OK, segera cairkan di Finance...';
                    message.name         = 'approval_message';
                    message.required     = false;
                    submitBtn.className  = 'btn btn-success';
                    submitBtn.innerHTML  = '<i class="bi bi-check-lg me-1"></i> Ya, Setujui';
                } else {
                    title.textContent    = 'Tolak Pengajuan';
                    subtitle.textContent = 'Mohon berikan alasan penolakan agar pemohon dapat memperbaikinya.';
                    label.innerHTML      = 'Alasan Penolakan <span class="text-danger">*</span>';
                    message.placeholder  = 'Tuliskan alasan penolakan...';
                    message.name         = 'rejection_reason';
                    message.required     = true;
                    submitBtn.className  = 'btn btn-danger';
                    submitBtn.innerHTML  = '<i class="bi bi-x-lg me-1"></i> Ya, Tolak';
                }
            });

            message.addEventListener('input', function () {
                charCount.textContent = this.value.length;
            });
        });
    </script>
</body>
</html>

~~~~

## FILE: resources/views/auth/login.blade.php

SHA-256: `511ac5718e2122a52d134a0c11dbe13c46723017607fe257223a90d41dff7277`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login EMMO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .login-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 35px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #efe;
            color: #363;
            border-left: 4px solid #363;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            user-select: none;
            background: none;
            border: none;
            padding: 4px;
        }

        .password-toggle:hover {
            color: #667eea;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🔐</div>
            <h1>Pintu Masuk EMMO</h1>
            <p>Silakan masuk untuk melanjutkan</p>
            @include('partials.application-time', ['mode' => 'plain'])
        </div>

        <div class="login-body">
            @if(session('error'))
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('proses_login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input 
                            type="text" 
                            id="username"
                            name="username" 
                            placeholder="Masukkan username Anda"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="icon">🔒</span>
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            placeholder="Masukkan password Anda"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Lihat password">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk</button>

                <p class="footer-text">EMMO &copy; {{ \App\Support\AppTime::now()->format('Y') }} - Semua hak dilindungi</p>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>

~~~~

## FILE: resources/views/cash-flow/index.blade.php

SHA-256: `34b7a47d7e03647efcacc9d7b90f41f6a2fae0431e469eca4e03655c618677b4`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Kas - Dashboard Keuangan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cash-flow.partials.styles')
</head>
<body class="cf-body">

    @include('partials.application-time')

    <div class="cf-wrap">

        @php
            Carbon\Carbon::setLocale('id');
            $rpSigned  = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
            $statusMap = \App\Models\Periode::whereIn('periode', $snapshots->pluck('periode'))
                            ->pluck('status', 'periode');
        @endphp

        {{-- ===== Header ===== --}}
        <div class="cf-header">
            <div>
                <h1 class="cf-title">Stok Kas</h1>
                <p class="cf-subtitle">
                    Gabungan BS dan event.
                    Sisa Saldo BS untuk belanja dihitung terpisah.
                </p>
            </div>
            <div class="cf-actions">
                <a href="{{ route('bs-balance.show') }}" class="btn-cf ghost">Rincian Saldo BS</a>
                <a href="{{ route('cash-flow.export') }}" class="btn-cf primary">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                </a>
                <a href="{{ route('dashboard.transaksi') }}" class="btn-cf ghost">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ===== Summary Periode Aktif ===== --}}
        @if($currentSnapshot)
            @php
                $currentStatus      = $statusMap[$currentPeriod] ?? 'OPEN';
                $isClosed           = $currentStatus === 'CLOSED';
                $showClosingBalance = $isClosed && $currentSnapshot->saldo_akhir !== null;
            @endphp

            <div class="cf-card accent">
                <div class="cf-card-head">
                    <div>
                        <div class="eyebrow">Periode Berjalan</div>
                        <h2 class="period-lg">
                            {{ str_replace('-', '', $currentPeriod) }}
                            <span>· {{ Carbon\Carbon::createFromFormat('!Y-m', $currentPeriod)->translatedFormat('F Y') }}</span>
                        </h2>
                    </div>
                    <span class="pill {{ $isClosed ? 'closed' : 'open' }}">
                        @if(!$isClosed)<span class="dot"></span>@endif
                        {{ $currentStatus }}
                    </span>
                </div>

                @if(!$currentSnapshot->opening_final)
                    <div class="cf-banner warn">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Saldo awal menunggu penutupan periode sebelumnya. Angka saat ini bersifat estimasi sementara.
                    </div>
                @endif

                @if($currentSnapshot->penyesuaian != 0)
                    <div class="cf-banner neutral">
                        <i class="bi bi-pencil-square"></i>
                        Penyesuaian Manual BS: <strong class="amt">{{ $rpSigned($currentSnapshot->penyesuaian) }}</strong>
                    </div>
                @endif

                <div class="cf-stats">
                    <div class="cf-stat">
                        <span class="cf-ico ico-blue"><i class="bi bi-wallet2"></i></span>
                        <span class="lbl">Saldo Awal</span>
                        <span class="val amt {{ $currentSnapshot->saldo_awal < 0 ? 'neg' : 'neu' }}">
                            {{ $rpSigned($currentSnapshot->saldo_awal) }}
                        </span>
                    </div>
                    <div class="cf-stat">
                        <span class="cf-ico ico-green"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span class="lbl">Total Masuk</span>
                        <span class="val amt pos">+ Rp {{ number_format($currentSnapshot->total_masuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="cf-stat">
                        <span class="cf-ico ico-red"><i class="bi bi-box-arrow-up"></i></span>
                        <span class="lbl">Total Keluar</span>
                        <span class="val amt neg">- Rp {{ number_format($currentSnapshot->total_keluar, 0, ',', '.') }}</span>
                    </div>
                    @if($showClosingBalance)
                        <div class="cf-stat">
                            <span class="cf-ico ico-slate"><i class="bi bi-cash-stack"></i></span>
                            <span class="lbl">Saldo Akhir</span>
                            <span class="val amt {{ $currentSnapshot->saldo_akhir < 0 ? 'neg' : 'neu' }}">
                                {{ $rpSigned($currentSnapshot->saldo_akhir) }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="bd-wrap">
                    @include('cash-flow.partials.expense-breakdown', ['snapshot' => $currentSnapshot])
                </div>
            </div>
        @endif

        {{-- ===== Riwayat Periode ===== --}}
        <div class="cf-section-head">
            <h3>
                <i class="bi bi-clock-history"></i> Riwayat Periode
                @if($snapshots->count() > 0)
                    <span class="cf-badge">{{ $snapshots->count() }}</span>
                @endif
            </h3>
        </div>

        @forelse($snapshots as $snapshot)
            @php
                $status      = $statusMap[$snapshot->periode] ?? 'OPEN';
                $isOpen      = $status === 'OPEN';
                $showEnd     = !$isOpen && $snapshot->saldo_akhir !== null;
                $periodLabel = str_replace('-', '', $snapshot->periode);
                $periodName  = Carbon\Carbon::createFromFormat('!Y-m', $snapshot->periode)->translatedFormat('M Y');
            @endphp

            <article class="h-card">
                <div class="h-top">
                    <div class="h-id">
                        <span class="cf-ico {{ $isOpen ? 'ico-blue' : 'ico-slate' }}">
                            <i class="bi {{ $isOpen ? 'bi-calendar2-check' : 'bi-archive' }}"></i>
                        </span>
                        <div>
                            <h4 class="h-title">{{ $periodLabel }} <span>· {{ $periodName }}</span></h4>
                            <div class="h-meta">
                                <i class="bi bi-stopwatch"></i>
                                Diperbarui: {{ $snapshot->last_updated?->diffForHumans() ?? 'belum ada update' }}
                            </div>
                        </div>
                    </div>

                    <div class="h-actions">
                        <span class="pill {{ $isOpen ? 'open' : 'closed' }} d-none d-md-inline-flex">{{ $status }}</span>
                        <a href="{{ route('cash-flow.show', $periodLabel) }}" class="btn-detail">
                            <i class="bi bi-eye-fill"></i> Detail
                        </a>
                        <a href="{{ route('cash-flow.export-period', $periodLabel) }}" class="btn-icon" title="Export Excel periode ini">
                            <i class="bi bi-file-earmark-excel"></i>
                        </a>
                    </div>
                </div>

                @if(!$snapshot->opening_final)
                    <div class="h-alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Saldo awal masih menunggu konfirmasi penutupan periode sebelumnya.
                    </div>
                @endif

                <div class="h-stats {{ $showEnd ? 'with-end' : '' }}">
                    <div class="h-stat">
                        <span class="lbl">Awal</span>
                        <span class="val amt {{ $snapshot->saldo_awal < 0 ? 'text-danger' : '' }}">
                            {{ $rpSigned($snapshot->saldo_awal) }}
                        </span>
                    </div>
                    <div class="h-stat">
                        <span class="lbl">Masuk</span>
                        <span class="val amt pos">+ Rp {{ number_format($snapshot->total_masuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-stat">
                        <span class="lbl">Keluar</span>
                        <span class="val amt neg">- Rp {{ number_format($snapshot->total_keluar, 0, ',', '.') }}</span>
                    </div>
                    @if($showEnd)
                        <div class="h-stat end">
                            <span class="lbl">Akhir</span>
                            <span class="val amt {{ $snapshot->saldo_akhir < 0 ? 'text-danger' : '' }}">
                                {{ $rpSigned($snapshot->saldo_akhir) }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($snapshot->penyesuaian != 0)
                    <div class="h-meta mt-2">
                        <i class="bi bi-pencil-square"></i>
                        Penyesuaian manual: <strong class="amt">{{ $rpSigned($snapshot->penyesuaian) }}</strong>
                    </div>
                @endif

                <div class="h-break">
                    @include('cash-flow.partials.expense-breakdown', ['snapshot' => $snapshot])
                </div>
            </article>
        @empty
            <div class="cf-empty">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Data Historis</h4>
                <p class="mb-4">Mulai catat transaksi pertama Anda untuk melihat laporan cash flow bulanan.</p>
                <a href="{{ route('transaksi.create') }}" class="btn-cf primary">
                    <i class="bi bi-plus-lg"></i> Buat Transaksi Baru
                </a>
            </div>
        @endforelse

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/cash-flow/partials/expense-breakdown.blade.php

SHA-256: `b33220d25d8f61b25caef8259080d82f78162b8d92fdb829aee757f20b97b30a`

~~~~php
{{-- Rincian arus kas — dipakai di index & show --}}

<div class="bd-grid" role="list" aria-label="Rincian arus kas periode ini">
    <div class="bd-cell bd-green" role="listitem">
        <span class="bd-label">Pencairan BS</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_masuk ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-red" role="listitem">
        <span class="bd-label">Pengeluaran BS</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_keluar_bs ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Dalam Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_dalam ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Luar Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_luar ?? 0, 0, ',', '.') }}</span>
    </div>
</div>

~~~~

## FILE: resources/views/cash-flow/partials/styles.blade.php

SHA-256: `4ca7635c331d387f19295f386523cf2f275d7fb3e50ac6d774382b285f8fb3ba`

~~~~php
{{-- =====================================================
     Design System bersama untuk modul Cash Flow (index & show)
     ===================================================== --}}
<style>
    :root{
        --bg:#f4f5f7;
        --surface:#ffffff;
        --surface-2:#f8fafc;
        --ink:#0f172a;
        --ink-2:#64748b;
        --ink-3:#94a3b8;
        --line:#e2e8f0;
        --line-2:#eef2f6;

        --blue:#2563eb;  --blue-soft:#eff6ff;
        --green:#059669; --green-soft:#ecfdf5;
        --red:#dc2626;   --red-soft:#fef2f2;
        --amber:#b45309; --amber-soft:#fffbeb;

        --r-lg:16px; --r-md:12px; --r-sm:10px;
        --sh-sm:0 1px 2px rgba(16,24,40,.05);
        --sh-md:0 10px 24px -8px rgba(16,24,40,.14);
    }

    body.cf-body{
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
        background:var(--bg);
        color:var(--ink);
        min-height:100vh;
        padding-bottom:4rem;
        -webkit-font-smoothing:antialiased;
    }

    .cf-wrap{max-width:1080px;margin-inline:auto;padding:1.5rem 1rem 0;}
    .cf-wrap.narrow{max-width:920px;}

    /* Angka monospace agar kolom Rupiah rapi */
    .amt{font-variant-numeric:tabular-nums lining-nums;letter-spacing:-.01em;white-space:nowrap;}

    /* ---------- Page header ---------- */
    .cf-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.75rem;}
    .cf-title{font-size:1.65rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .3rem;}
    .cf-subtitle{font-size:.9rem;color:var(--ink-2);margin:0;max-width:62ch;line-height:1.55;}
    .cf-actions{display:flex;gap:.6rem;flex-wrap:wrap;}

    .btn-cf{display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;font-weight:600;font-size:.85rem;
            padding:.55rem 1rem;text-decoration:none;transition:.18s ease;border:1px solid transparent;}
    .btn-cf i{font-size:.95rem;}
    .btn-cf.primary{background:var(--green);color:#fff;box-shadow:var(--sh-sm);}
    .btn-cf.primary:hover{background:#047857;color:#fff;transform:translateY(-1px);}
    .btn-cf.ghost{background:var(--surface);border-color:var(--line);color:var(--ink-2);}
    .btn-cf.ghost:hover{background:var(--surface-2);color:var(--ink);border-color:#cbd5e1;}

    /* ---------- Card shell ---------- */
    .cf-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);
             box-shadow:var(--sh-sm);overflow:hidden;margin-bottom:1.25rem;}
    .cf-card.accent{border-left:4px solid var(--blue);}

    .cf-card-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;
                  padding:1.15rem 1.5rem;background:linear-gradient(90deg,var(--surface-2),#fff);border-bottom:1px solid var(--line);}
    .cf-head-title{display:flex;align-items:center;gap:.7rem;margin:0;font-size:1.02rem;font-weight:700;}
    .cf-head-title .sub{font-size:.78rem;font-weight:500;color:var(--ink-2);}
    .cf-badge{background:#e8edf3;color:#475569;font-size:.7rem;font-weight:700;padding:.22rem .6rem;border-radius:999px;white-space:nowrap;}

    .cf-ico{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .ico-blue{background:var(--blue-soft);color:var(--blue);}
    .ico-green{background:var(--green-soft);color:var(--green);}
    .ico-red{background:var(--red-soft);color:var(--red);}
    .ico-amber{background:var(--amber-soft);color:var(--amber);}
    .ico-slate{background:#f1f5f9;color:var(--ink-2);}

    /* ---------- Status pill ---------- */
    .pill{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;font-size:.7rem;font-weight:800;
          letter-spacing:.06em;padding:.34rem .75rem;text-transform:uppercase;}
    .pill.open{background:var(--green-soft);color:var(--green);}
    .pill.closed{background:#f1f5f9;color:var(--ink-2);}
    .pill .dot{width:6px;height:6px;border-radius:50%;background:currentColor;animation:cf-pulse 2s infinite;}
    @keyframes cf-pulse{0%{box-shadow:0 0 0 0 rgba(5,150,105,.45)}70%{box-shadow:0 0 0 6px rgba(5,150,105,0)}100%{box-shadow:0 0 0 0 rgba(5,150,105,0)}}

    /* ---------- Banners ---------- */
    .cf-banner{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;font-size:.83rem;padding:.7rem 1.5rem;border-top:1px solid var(--line);}
    .cf-banner.warn{background:var(--amber-soft);border-top-color:#fde68a;color:#92400e;}
    .cf-banner.neutral{background:var(--surface-2);color:var(--ink-2);}

    /* ---------- Summary stats ---------- */
    .eyebrow{font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:.15rem;}
    .period-lg{font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin:0;}
    .period-lg span{font-size:.9rem;font-weight:500;color:var(--ink-2);}

    .cf-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1.1rem;padding:1.35rem 1.5rem;}
    .cf-stat{display:flex;flex-direction:column;gap:.3rem;min-width:0;}
    .cf-stat .cf-ico{width:30px;height:30px;border-radius:8px;margin-bottom:.4rem;}
    .cf-stat .lbl{font-size:.74rem;font-weight:700;color:var(--ink-2);text-transform:uppercase;letter-spacing:.05em;}
    .cf-stat .val{font-size:1.22rem;font-weight:800;letter-spacing:-.02em;overflow-wrap:anywhere;}
    .cf-stat .val.pos{color:var(--green);} .cf-stat .val.neg{color:var(--red);} .cf-stat .val.neu{color:var(--ink);}
    @media(min-width:768px){ .cf-stat + .cf-stat{border-left:1px solid var(--line-2);padding-left:1.1rem;} }

    /* ---------- Breakdown grid (partial expense-breakdown) ---------- */
    .bd-wrap{padding:0 1.5rem 1.4rem;}
    .bd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.6rem;}
    .bd-cell{background:var(--surface-2);border:1px solid var(--line-2);border-left:3px solid var(--ink-3);
             border-radius:var(--r-sm);padding:.6rem .8rem;display:flex;flex-direction:column;gap:.15rem;min-width:0;}
    .bd-cell .bd-label{font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .bd-cell .bd-value{font-size:.92rem;font-weight:800;color:var(--ink);}
    .bd-green{border-left-color:var(--green);} .bd-green .bd-value{color:var(--green);}
    .bd-blue{border-left-color:var(--blue);}   .bd-blue  .bd-value{color:var(--blue);}
    .bd-red{border-left-color:var(--red);}     .bd-red   .bd-value{color:var(--red);}
    .bd-amber{border-left-color:#f59e0b;}
    .bd-slate{border-left-color:#94a3b8;}
    .bd-note{display:flex;align-items:center;gap:.45rem;font-size:.78rem;color:var(--ink-3);margin:.7rem 0 0;}

    /* ---------- History list (index) ---------- */
    .cf-section-head{display:flex;align-items:center;gap:.6rem;margin:2rem 0 1rem;}
    .cf-section-head h3{margin:0;font-size:1.05rem;font-weight:800;letter-spacing:-.01em;display:flex;align-items:center;gap:.55rem;}
    .cf-section-head h3 i{color:var(--ink-3);}

    .h-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-md);padding:1.1rem 1.25rem;margin-bottom:.9rem;transition:.18s ease;}
    .h-card:hover{border-color:#cbd5e1;box-shadow:var(--sh-md);transform:translateY(-2px);}
    .h-top{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:.9rem;}
    .h-id{display:flex;gap:.9rem;align-items:center;min-width:0;}
    .h-id .cf-ico{width:40px;height:40px;border-radius:11px;font-size:1.05rem;}
    .h-title{font-size:1rem;font-weight:800;margin:0;letter-spacing:-.01em;}
    .h-title span{font-weight:500;color:var(--ink-2);font-size:.82rem;}
    .h-meta{display:flex;align-items:center;gap:.4rem;font-size:.74rem;color:var(--ink-3);margin-top:.2rem;}
    .h-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
    .btn-detail{display:inline-flex;align-items:center;gap:.4rem;background:var(--blue);color:#fff;border-radius:8px;
                font-size:.8rem;font-weight:700;padding:.42rem .85rem;text-decoration:none;transition:.15s;}
    .btn-detail:hover{background:#1d4ed8;color:#fff;}
    .btn-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid var(--line);
              border-radius:8px;color:var(--ink-2);background:transparent;text-decoration:none;transition:.15s;}
    .btn-icon:hover{color:var(--green);border-color:#bbf7d0;background:var(--green-soft);}

    .h-alert{display:flex;gap:.5rem;align-items:center;background:var(--amber-soft);border:1px solid #fde68a;color:#92400e;
             border-radius:8px;font-size:.78rem;padding:.45rem .7rem;margin-bottom:.8rem;}

    .h-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.9rem;background:var(--surface-2);
             border:1px solid var(--line-2);border-radius:var(--r-sm);padding:.7rem .95rem;}
    @media(min-width:768px){ .h-stats.with-end{grid-template-columns:repeat(4,1fr);} }
    .h-stat{display:flex;flex-direction:column;gap:.1rem;min-width:0;}
    .h-stat .lbl{font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .h-stat .val{font-size:.88rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .h-stat .val.pos{color:var(--green);} .h-stat .val.neg{color:var(--red);}
    .h-stat.end{border-left:1px solid var(--line);padding-left:.9rem;}
    .h-break{margin-top:.95rem;padding-top:.95rem;border-top:1px dashed var(--line);}

    /* ---------- Transaction list (show) ---------- */
    .trx-list{padding:.85rem 1rem;}
    .trx{display:flex;align-items:center;gap:.85rem;padding:.7rem .8rem;border:1px solid var(--line-2);
         border-radius:var(--r-sm);background:#fff;margin-bottom:.5rem;transition:.15s;}
    .trx:last-child{margin-bottom:0;}
    .trx:hover{border-color:var(--line);background:var(--surface-2);}
    .trx-link{color:inherit;text-decoration:none;}
    .trx-link:hover{color:inherit;border-color:var(--blue);background:var(--blue-soft);}
    .trx-link:focus-visible{outline:2px solid var(--blue);outline-offset:-3px;background:var(--blue-soft);}
    .trx-open{display:inline-flex;align-items:center;gap:.35rem;margin-top:.3rem;font-size:.72rem;font-weight:700;color:var(--blue);}
    .trx .cf-ico{width:36px;height:36px;}
    .trx-body{flex:1;min-width:0;}
    .trx-type{font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .trx-desc{font-size:.88rem;font-weight:600;overflow-wrap:anywhere;}
    .trx-date{display:flex;align-items:center;gap:.35rem;font-size:.72rem;color:var(--ink-3);margin-top:.15rem;}
    .trx-amt{font-weight:800;font-size:.92rem;}
    .trx-amt.pos{color:var(--green);} .trx-amt.neg{color:var(--red);} .trx-amt.neu{color:var(--ink);}
    .trx-foot{display:flex;justify-content:flex-end;align-items:baseline;gap:.6rem;padding:.8rem 1rem;
              border-top:1px solid var(--line-2);background:var(--surface-2);font-size:.85rem;}
    .trx-foot .lbl{color:var(--ink-2);font-weight:600;}
    .trx-foot .val{font-size:1rem;font-weight:800;}
    .trx-foot .val.pos{color:var(--green);} .trx-foot .val.neg{color:var(--red);}

    /* ---------- Empty state ---------- */
    .cf-empty{text-align:center;padding:3.5rem 1.5rem;color:var(--ink-2);}
    .cf-empty i{font-size:2.6rem;color:#cbd5e1;display:block;margin-bottom:.8rem;}
    .cf-empty h4{font-weight:800;color:var(--ink);margin-bottom:.4rem;}
    .cf-empty.sm{padding:2.2rem 1rem;font-size:.88rem;}
    .cf-empty.sm i{font-size:2rem;margin-bottom:.5rem;}

    /* ---------- Responsive ---------- */
    @media(max-width:576px){
        .cf-stats{padding:1.1rem;gap:.9rem;}
        .cf-stat + .cf-stat{border-left:0;padding-left:0;}
        .bd-wrap{padding:0 1.1rem 1.2rem;}
        .cf-card-head{padding:1rem 1.1rem;}
        .cf-banner{padding-left:1.1rem;padding-right:1.1rem;}
        .trx{flex-wrap:wrap;}
        .trx-amt{width:100%;text-align:right;margin-left:44px;}
        .h-top{flex-direction:column;}
        .h-actions{width:100%;justify-content:flex-end;}
    }
</style>

~~~~

## FILE: resources/views/cash-flow/partials/transaction-section.blade.php

SHA-256: `383945a0f687fb061994202460ec62c9fc7e2336eb867da2801063a445342ef6`

~~~~php
@php
    Carbon\Carbon::setLocale('id');
    $rpSigned = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');

    $iconMap = [
        'masuk' => ['icon' => 'bi-arrow-down-circle', 'ico-class' => 'ico-green', 'item-icon' => 'bi-box-arrow-in-down', 'sign' => '+', 'amt-class' => 'pos'],
        'keluar' => ['icon' => 'bi-arrow-up-circle', 'ico-class' => 'ico-red', 'item-icon' => 'bi-box-arrow-up', 'sign' => '-', 'amt-class' => 'neg'],
    ];
    $meta = $iconMap[$section];
    $title = $section === 'masuk' ? 'Uang Masuk' : 'Uang Keluar';
@endphp

<div class="cf-card-head">
    <h3 class="cf-head-title">
        <span class="cf-ico {{ $meta['ico-class'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
        {{ $title }}
    </h3>
    <span class="cf-badge">{{ $paginator->total() }} transaksi</span>
</div>

@if($paginator->total() > 0)
    <div class="trx-list">
        @foreach($paginator->items() as $trx)
            @if(!empty($trx['url']))
                <a href="{{ $trx['url'] }}" class="trx trx-item trx-link">
            @else
                <div class="trx trx-item">
            @endif
                <span class="cf-ico {{ $meta['ico-class'] }}"><i class="bi {{ $meta['item-icon'] }}"></i></span>
                <div class="trx-body">
                    <div class="trx-type">{{ $trx['type'] }}</div>
                    <div class="trx-desc">{{ $trx['description'] }}</div>
                    <div class="trx-date">
                        <i class="bi bi-calendar3"></i>
                        {{ Carbon\Carbon::parse($trx['date'])->translatedFormat('d M Y') }}
                    </div>
                    @if(!empty($trx['url']))
                        <span class="trx-open">Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                    @endif
                </div>
                <div class="trx-amt {{ $meta['amt-class'] }} amt">
                    {{ $meta['sign'] }} Rp {{ number_format($trx['nominal'], 0, ',', '.') }}
                </div>
            @if(!empty($trx['url']))</a>@else</div>@endif
        @endforeach
    </div>

    @if($paginator->count() > 0)
        <div class="trx-range">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} transaksi
        </div>
    @endif

    @if($paginator->hasPages())
        <div class="trx-paginate">
            {!! $paginator->appends([$otherSection . '_page' => $otherPage])
                         ->fragment($sectionId)
                         ->links('pagination.bootstrap-5-htmx') !!}
        </div>
    @endif

    <div class="trx-foot">
        <span class="lbl">Total {{ $title }}:</span>
        <span class="val {{ $meta['amt-class'] }} amt">
            {{ $meta['sign'] }} Rp {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>
@else
    <div class="cf-empty sm">
        <i class="bi bi-inbox"></i>
        Tidak ada {{ strtolower($title) }} di periode ini
    </div>
@endif
~~~~

## FILE: resources/views/cash-flow/show.blade.php

SHA-256: `053a8f0d2e51ff3324fef7840392c28c2c42f6b79635e57250f9bf85492fc9d7`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    @php Carbon\Carbon::setLocale('id'); @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Cash Flow - {{ Carbon\Carbon::createFromFormat('!Y-m', $periode)->translatedFormat('F Y') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cash-flow.partials.styles')

    {{-- ========================================================== --}}
    {{-- CSS TAMBAHAN untuk pagination & fragment anchor            --}}
    {{-- ========================================================== --}}
    <style>
        /* Agar saat mendarat via fragment (#sec-masuk), puncak card
           tidak tertutup bar sticky/fixed. Sesuaikan dgn tinggi bar Anda. */
        .cf-card { scroll-margin-top: 76px; }

        /* Info "Menampilkan x–y dari z" */
        .trx-range {
            text-align: center;
            font-size: .75rem;
            color: #64748b;
            padding: .75rem 0 0;
        }

        /* Pembungkus paginator agar rapi di tengah */
        .trx-paginate {
            display: flex;
            justify-content: center;
            padding: .5rem 0 .75rem;
        }
        .trx-paginate .pagination { margin-bottom: 0; }

        /* Penyesuaian BS tetap pakai progressive disclosure (biasanya sedikit item) */
        .trx-list.is-collapsed .trx-item:nth-child(n+6) { display: none; }
        .trx-list { position: relative; }
        .trx-list.is-collapsed::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 64px;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, #ffffff 95%);
            pointer-events: none;
        }
        .trx-more {
            display: flex;
            justify-content: center;
            padding: .5rem 0 .75rem;
        }
        .btn-toggle-list {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-width: 240px;
        }
        .btn-toggle-list .bi { transition: transform .2s ease; }
        .btn-toggle-list.expanded .bi { transform: rotate(180deg); }
        /* Indicator saat HTMX sedang memuat */
.htmx-request .trx-list,
.htmx-request .trx-paginate {
    opacity: 0.4;
    transition: opacity 0.15s ease;
    pointer-events: none;
}

/* Indikator "spinner" kecil di card yang sedang loading */
.cf-card.htmx-request::before {
    content: '';
    position: absolute;
    top: 12px; right: 12px;
    width: 18px; height: 18px;
    border: 2px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    z-index: 5;
}
@keyframes spin { to { transform: rotate(360deg); } }

.cf-card { position: relative; }
    </style>
</head>
<body class="cf-body">

    @include('partials.application-time')

    <div class="cf-wrap narrow">

        @php
            $rpSigned           = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
            $isClosed           = $status === 'CLOSED';
            $showClosingBalance = $isClosed && $snapshot->saldo_akhir !== null;
            $periodLabel        = str_replace('-', '', $periode);
            $periodName         = Carbon\Carbon::createFromFormat('!Y-m', $periode)->translatedFormat('F Y');
        @endphp

        {{-- ===== Header ===== --}}
        <div class="cf-header">
            <div>
                <h1 class="cf-title">Detail Cash Flow</h1>
                <p class="cf-subtitle">
                    {{ $periodLabel }} · {{ $periodName }} — saldo laporan mencakup BS dan event
                    sesuai rincian. Sisa Saldo BS dihitung terpisah.
                </p>
            </div>
            <div class="cf-actions">
                <a href="{{ route('bs-balance.show', ['periode' => $periode]) }}" class="btn-cf ghost">Rincian Saldo BS</a>
                <a href="{{ route('cash-flow.export-period', $periodLabel) }}" class="btn-cf primary" title="Export periode ini ke Excel">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <a href="{{ route('cash-flow.index') }}" class="btn-cf ghost">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ===== Ringkasan ===== --}}
        <div class="cf-card accent">
            <div class="cf-card-head">
                <div>
                    <div class="eyebrow">Ringkasan Stok Kas</div>
                    <h2 class="period-lg">{{ $periodLabel }} <span>· {{ $periodName }}</span></h2>
                </div>
                <span class="pill {{ $isClosed ? 'closed' : 'open' }}">
                    @if(!$isClosed)<span class="dot"></span>@endif
                    {{ $status }}
                </span>
            </div>

            @if(!$snapshot->opening_final)
                <div class="cf-banner warn">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Saldo awal menunggu penutupan periode sebelumnya.
                </div>
            @endif

            @if($snapshot->penyesuaian != 0)
                <div class="cf-banner neutral">
                    <i class="bi bi-pencil-square"></i>
                    Termasuk penyesuaian BS: <strong class="amt">{{ $rpSigned($snapshot->penyesuaian) }}</strong>
                </div>
            @endif

            <div class="cf-stats">
                <div class="cf-stat">
                    <span class="lbl">Saldo Awal</span>
                    <span class="val amt {{ $snapshot->saldo_awal < 0 ? 'neg' : 'neu' }}">{{ $rpSigned($snapshot->saldo_awal) }}</span>
                </div>
                <div class="cf-stat">
                    <span class="lbl">Total Masuk</span>
                    <span class="val amt pos">+ Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span>
                </div>
                <div class="cf-stat">
                    <span class="lbl">Total Keluar</span>
                    <span class="val amt neg">- Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span>
                </div>
                @if($showClosingBalance)
                    <div class="cf-stat">
                        <span class="lbl">Saldo Akhir</span>
                        <span class="val amt {{ $snapshot->saldo_akhir < 0 ? 'neg' : 'neu' }}">{{ $rpSigned($snapshot->saldo_akhir) }}</span>
                    </div>
                @endif
            </div>

            <div class="bd-wrap">
                @include('cash-flow.partials.expense-breakdown', ['snapshot' => $snapshot])
            </div>
        </div>

        {{-- ===== Penyesuaian BS (tanpa pagination, jumlah item biasanya sedikit) ===== --}}
        @if(count($transactions['penyesuaian']) > 0)
            @php $countPenyesuaian = count($transactions['penyesuaian']); @endphp
            <div class="cf-card" id="sec-penyesuaian">
                <div class="cf-card-head">
                    <h3 class="cf-head-title">
                        <span class="cf-ico ico-amber"><i class="bi bi-pencil-square"></i></span>
                        Penyesuaian BS
                    </h3>
                    <span class="cf-badge">{{ $countPenyesuaian }} item</span>
                </div>

                <div class="trx-list {{ $countPenyesuaian > 5 ? 'is-collapsed' : '' }}" id="list-penyesuaian">
                    @foreach($transactions['penyesuaian'] as $trx)
                        @if(!empty($trx['url']))
                            <a href="{{ $trx['url'] }}" class="trx trx-item trx-link">
                        @else
                            <div class="trx trx-item">
                        @endif
                            <span class="cf-ico ico-amber"><i class="bi bi-arrow-left-right"></i></span>
                            <div class="trx-body">
                                <div class="trx-desc">{{ $trx['description'] }}</div>
                                @if(!empty($trx['url']))
                                    <span class="trx-open">Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                                @endif
                            </div>
                            <div class="trx-amt neu amt">{{ $rpSigned($trx['nominal']) }}</div>
                        @if(!empty($trx['url']))</a>@else</div>@endif
                    @endforeach
                </div>

                @if($countPenyesuaian > 5)
                    <div class="trx-more">
                        <button type="button" class="btn-cf ghost btn-toggle-list"
                                data-target="list-penyesuaian"
                                data-more="Lihat semua ({{ $countPenyesuaian }} item)"
                                data-less="Sembunyikan sebagian"
                                aria-expanded="false" aria-controls="list-penyesuaian">
                            <i class="bi bi-chevron-down"></i>
                            <span class="lbl-toggle">Lihat semua ({{ $countPenyesuaian }} item)</span>
                        </button>
                    </div>
                @endif

                <div class="trx-foot">
                    <span class="lbl">Total Penyesuaian:</span>
                    <span class="val amt">{{ $rpSigned($totalPenyesuaian) }}</span>
                </div>
            </div>
        @endif

        {{-- ===== Uang Masuk (HTMX-powered pagination) ===== --}}
<div class="cf-card" id="sec-masuk">
    <div
        id="sec-masuk-content"
        hx-get="{{ route('cash-flow.transactions-partial', ['periode' => $periode, 'section' => 'masuk', 'masuk_page' => $pagedMasuk->currentPage(), 'keluar_page' => $pagedKeluar->currentPage()]) }}"
        hx-trigger="load"
        hx-swap="innerHTML"
    >
        {{-- Konten initial dari server (rendered server-side) --}}
        @include('cash-flow.partials.transaction-section', [
            'section' => 'masuk',
            'paginator' => $pagedMasuk,
            'total' => $totalMasuk,
            'totalLabel' => $totalMasuk,
            'sectionId' => 'sec-masuk',
            'otherPage' => $pagedKeluar->currentPage(),
            'otherSection' => 'keluar',
        ])
    </div>
</div>

{{-- ===== Uang Keluar (HTMX-powered pagination) ===== --}}
<div class="cf-card" id="sec-keluar">
    <div
        id="sec-keluar-content"
        hx-get="{{ route('cash-flow.transactions-partial', ['periode' => $periode, 'section' => 'keluar', 'masuk_page' => $pagedMasuk->currentPage(), 'keluar_page' => $pagedKeluar->currentPage()]) }}"
        hx-trigger="load"
        hx-swap="innerHTML"
    >
        @include('cash-flow.partials.transaction-section', [
            'section' => 'keluar',
            'paginator' => $pagedKeluar,
            'total' => $totalKeluar,
            'totalLabel' => $totalKeluar,
            'sectionId' => 'sec-keluar',
            'otherPage' => $pagedMasuk->currentPage(),
            'otherSection' => 'masuk',
        ])
    </div>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS untuk Penyesuaian BS (tetap pakai progressive disclosure) --}}
    <script>
        document.querySelectorAll('.btn-toggle-list').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const list = document.getElementById(btn.dataset.target);
                if (!list) return;

                const card = list.closest('.cf-card');
                const isCollapsed = list.classList.toggle('is-collapsed');
                btn.querySelector('.lbl-toggle').textContent = isCollapsed ? btn.dataset.more : btn.dataset.less;
                btn.classList.toggle('expanded', !isCollapsed);
                btn.setAttribute('aria-expanded', String(!isCollapsed));

                if (isCollapsed && card) {
                    const rect = card.getBoundingClientRect();
                    if (rect.top < 0) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    </script>
</body>
</html>
~~~~

## FILE: resources/views/companies/create.blade.php

SHA-256: `905965a150d04485db3c47666b80c2aad0dd89d91f13376943b9a5d91a45a3ed`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Company</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('companies.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Company Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: PT_ABC, CV_MAJU">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Perusahaan / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: PT. Angin Ribut Sejahtera">{{ old('nama') }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Company</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Company</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Perusahaan</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                            <tr>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->code }}</td>
                                <td>{{ $company->nama }}</td>
                                <td>
                                    <span class="badge {{ $company->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $company->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('companies.destroy', $company->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus perusahaan ini?')">
                                    Hapus
                                </button>
                            </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/companies/edit.blade.php

SHA-256: `26b62da6dbd952d03415ddcd97320ddfb00f542825fa3e66683b9888271d53f0`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Company</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('companies.update', $company->id) }}" method="POST">
                    @csrf 
                    @method('PUT') <div class="mb-3">
    <label for="code" class="form-label">Company Code</label>
    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $company->code) }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Perusahaan / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" required>{{ $company->nama }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $company->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('companies.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Company</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/cost_categories/create.blade.php

SHA-256: `38928d58753f01b3ce3a02e17032bf6081f14d3ffcebc81aa4c05d8ef57070bf`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Cost Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Cost Category<h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('cost_categories.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Cost Category Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: HTL, OPRT, ADMN">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Nama Cost Category / Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Contoh: Hotel, Operation, Administration">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Cost Category</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Cost Category</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Cost Category</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costCategories as $costCategory)
                            <tr>
                                <td>{{ $costCategory->id }}</td>
                                <td>{{ $costCategory->nama }}</td>
                                <td>{{ $costCategory->description }}</td>
                                <td>
                                    <span class="badge {{ $costCategory->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $costCategory->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('cost_categories.edit', $costCategory->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('cost_categories.destroy', $costCategory->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus position ini?')">
                                    Hapus
                                </button>
                            </form>
                                </td> 
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/cost_categories/edit.blade.php

SHA-256: `f53a35e84f63718d20031e825c2ffcc3bf6978a422b8eb576a48c3a2c0ee2360`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Cost Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Cost Category</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('cost_categories.update', $costCategories->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Cost Category Code</label>
                        <!-- Hapus 'readonly', tambahkan validasi error dan old() -->
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $costCategories->code) }}">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Nama Cost Category / Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $costCategories->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $costCategories->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cost_categories.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Cost Category</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/event_hour_groups/create.blade.php

SHA-256: `0af52171afab4a6e7e8a92cd6bb95b3573e8e2068ed40d3606a135f108ccd635`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Event Hour Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Event Hour Group</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('event_hour_groups.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label class="form-label">Code (Jam)</label>
                        <select name="code" class="form-select @error('code') is-invalid @enderror">
                            <option value="">-- Pilih Jam --</option>
                            <option value="8 Jam" {{ old('code') == '8 Jam' ? 'selected' : '' }}>8 Jam</option>
                            <option value="12 Jam" {{ old('code') == '12 Jam' ? 'selected' : '' }}>12 Jam</option>
                            <option value="16 Jam" {{ old('code') == '16 Jam' ? 'selected' : '' }}>16 Jam</option>
                        </select>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Contoh: Shift Standar">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Event Hour Group</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Event Hour Group</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventHourGroups as $ehg)
                            <tr>
                                <td>{{ $ehg->id }}</td>
                                <td>{{ $ehg->code ?? '-' }}</td>
                                <td>{{ $ehg->description ?? '-' }}</td>
                                <td><span class="badge {{ $ehg->is_active ? 'bg-success' : 'bg-danger' }}">{{ $ehg->is_active ? 'Aktif' : 'Non-Aktif' }}</span></td>
                                <td>
                                    <a href="{{ route('event_hour_groups.edit', $ehg->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                    <form action="{{ route('event_hour_groups.destroy', $ehg->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/event_hour_groups/edit.blade.php

SHA-256: `8b265f796100a6f66201936af9d5df307365d4fecb388cca7586e7d798d7918c`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Event Hour Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Event Hour Group</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('event_hour_groups.update', $eventHourGroup->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 

                    <div class="mb-3">
                        <label class="form-label">Code (Jam)</label>
                        <select name="code" class="form-select @error('code') is-invalid @enderror">
                            <option value="">-- Pilih Jam --</option>
                            <option value="8 Jam" {{ old('code', $eventHourGroup->code) == '8 Jam' ? 'selected' : '' }}>8 Jam</option>
                            <option value="12 Jam" {{ old('code', $eventHourGroup->code) == '12 Jam' ? 'selected' : '' }}>12 Jam</option>
                            <option value="16 Jam" {{ old('code', $eventHourGroup->code) == '16 Jam' ? 'selected' : '' }}>16 Jam</option>
                        </select>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $eventHourGroup->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $eventHourGroup->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('event_hour_groups.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Event Hour Group</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/levels/create.blade.php

SHA-256: `45781017853dae71708070a143dd1c6cd4288262871cc534ab7a8917da177ff4`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Level</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Level</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('levels.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Level Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: NONSTAFF, STAFF, SPV, JM, MGR">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Perusahaan / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: Manager, Supervisor">{{ old('nama') }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Level</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Level</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Level</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levels as $level)
                            <tr>
                                <td>{{ $level->id }}</td>
                                <td>{{ $level->code }}</td>
                                <td>{{ $level->nama }}</td>
                                <td>
                                    <span class="badge {{ $level->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $level->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('levels.edit', $level->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('levels.destroy', $level->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus perusahaan ini?')">
                                    Hapus
                                </button>
                            </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/levels/edit.blade.php

SHA-256: `863d89878e599b4a2202b2a3ebc6e15001c8eed63e3a6b7db9de6e1ea4ce6b48`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Level</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Level</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('levels.update', $level->id) }}" method="POST">
                    @csrf 
                    @method('PUT') <div class="mb-3">
                        <label for="code" class="form-label">Level Code</label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ $level->code }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Level / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" required>{{ $level->nama }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $level->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('levels.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Level</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>

~~~~
