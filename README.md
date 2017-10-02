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

Once the extension is installed, simply use it in your code by adding actions to any of yours controllers  :

```php
    /** Listner action **/        
    public function actionTaskProcessor() {
        global $argv;
        $old     = $argv;
        $argv[0] = $old[1];
        $argv[1] = $argv[2];
        if (array_key_exists(3, $argv)) {
            $argv[2] = $argv[3];
        }
        $processor = Yii::$app->processor;
        $processor->listen();
    }
    
    /** Runner deffered task processor
     * 
     * @param string $task task name form config
     */
    public function actionTaskProcessorRun($task) {
        global $argv;
        $old     = $argv;
        $argv[0] = $old[1];
        if (array_key_exists(3, $argv)) {
            $argv[1] = $argv[3];
        }
        if (array_key_exists(4, $argv)) {
            $argv[2] = $argv[4];
        }
        $processor = Yii::$app->processor;
        $processor->run($task);
    }    
```


example config:
```php
'processor'  => [
            'class'          => 'dostoevskiy\processor\SmartTaskProcessor',
            'tasksConfig'    => [
                'statistics'   => [
                    'class'          => 'console\components\taskProcessor\statistics\StatsProcessor',
                    'type'           => 'deferred',
                    'threads'        => 3,
                    'storage'        => 'rabbit',
                    'storageOptions' => [
                        'durable'    => false,
                        'queue'      => 'statistics',
                        'persistent' => false
                    ],
                ],
                'linkDelivery' => [
                    'class'          => 'console\components\taskProcessor\links\LinksDelivery',
                    'type'           => 'live',
                    'threads'        => 1,
                    'transactional'  => true,
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
                'host'             => '0.0.0.0',
                'port'             => '8181',
                'threads'          => 8,
                'servicesToReload' => ['db', 'mongo', 'rabbit'],
            ],
        ],
```
