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
            'class'               => 'dostoevskiy\processor\SmartTaskProcessor',
            'type'                => 'deferred',
            'storageType'         => 'rabbit',
            'storageOptions'      => [
                'host'     => 'localhost',
                'port'     => 5672,
                'user'     => 'guest',
                'password' => 'guest',
                'vhost'    => '/',
            ],
            'taskProcessorConfig' => [
                'class' => 'console\components\statistics\StatsProcessor'
            ],
            'listenOptions'       => [
                'class'            => 'dostoevskiy\processor\src\classes\Listner',
                'host'             => '127.0.0.1',
                'port'             => '1488',
                'count'            => 2,
                'type'             => 'tcp',
                'servicesToReload' => ['db']
            ]
        ],
```