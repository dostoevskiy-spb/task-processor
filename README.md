Smart Task Processor
====================
Smart Task Processor with ability to live or deferred processing tasks through any of availabled transports: nats, rabbitmq, mongo, native socket

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dostoevskiy-spb/yii2-smart-task-processor "*"
```

or add

```
"dostoevskiy-spb/yii2-smart-task-processor": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \dostoevskiy\tools\AutoloadExample::widget(); ?>```
```

example config:
```php
'processor'  => [
            'class'          => 'dostoevskiy\processor\SmartTaskProcessor',
            'tasksConfig'    => [
                'statistics' => [
                    'class'          => 'console\components\statistics\StatsProcessor',
                    'type'           => 'deferred',
                    'threads'        => 1,
                    'storage'        => 'rabbit',
                    'storageOptions' => [
                        'durable'    => false,
                        'queue'      => 'statistics',
                        'persistent' => false
                    ],
                ],
            ],
            'storagesConfig' => [
                'rabbit' => [
                    'type'        => 'rabbit',
                    'credentials' => [
                        'host'     => 'localhost',
                        'port'     => 5672,
                        'user'     => 'guest',
                        'password' => 'guest',
                        'vhost'    => '/',
                    ],
                ],
            ],
            'listnerConfig'  => [
                'class'            => 'dostoevskiy\processor\src\classes\Listner',
                'type'             => 'tcp',
                'host'             => '127.0.0.1',
                'port'             => '1488',
                'threads'          => 1,
                'servicesToReload' => ['db'],
            ],
        ],
    ],
```