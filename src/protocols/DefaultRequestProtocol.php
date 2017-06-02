<?php
namespace dostoevskiy\processor\src\protocols;

use dostoevskiy\processor\src\classes\AbstractTask;
use dostoevskiy\processor\src\helpers\HttpResponse;
use dostoevskiy\processor\src\interfaces\RequestProtocolInterface;
use yii\helpers\ArrayHelper;

class DefaultRequestProtocol implements RequestProtocolInterface
{

    public function getProcessRequestCallback($tasks)
    {
        return function ($connection, $data) use ($tasks) {
            /** @var $connection \Workerman\Connection\ConnectionInterface */
            $body     = explode("\r\n", $data);
            $body     = json_decode(array_pop($body), true, 1024);
            $taskName = ArrayHelper::getValue($body, 'task', false);
            $taskData = ArrayHelper::getValue($body, 'data', false);
            if (!$taskName) {
                $resp = new HttpResponse(['status' => 'error', 'error' => 'Task directive is missing'], 400);
                $connection->send($resp->asRaw());
                $connection->close();

                return;
            }
            if (!$taskData) {
                $resp = new HttpResponse(['status' => 'error', 'error' => 'Task data is missing'], 400);
                $connection->send($resp->asRaw());
                $connection->close();

                return;
            }
            /** @var AbstractTask $taskInstance */
            $taskInstance = ArrayHelper::getValue($tasks, $taskName);
            if (!$taskInstance) {
                $resp = new HttpResponse(['status' => 'error', 'error' => "Unknown task $taskName"], 404);
                $connection->send($resp->asRaw());
                $connection->close();

                return;
            }
            if ($taskInstance->isLive()) {
                if ($taskInstance->isTransactional()) {
                    $taskInstance->prepare($taskData);
                    $resp = new HttpResponse($taskInstance->process($taskData));
                    $connection->send($resp->asRaw());
                    $connection->close();

                    return;
                } else {
                    $resp = new HttpResponse(['status' => 'success']);
                    $connection->send($resp->asRaw());
                    $connection->close();
                    $taskInstance->prepare($taskData);
                    $taskInstance->process($data);

                    return;
                }
            } else {
                $storageInstance = $taskInstance->storage;
                $resp = new HttpResponse(['status' => 'success']);
                if ($taskInstance->isTransactional()) {
                    $storageInstance->push($taskName, $taskData);
                    $connection->send($resp->asRaw());
                    $connection->close();


                    return;
                } else {
                    $connection->send($resp->asRaw());
                    $connection->close();
                    $storageInstance->push($taskName, $taskData);

                    return;
                }
            }
        };
    }
}
