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