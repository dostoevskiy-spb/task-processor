<?php
namespace dostoevskiy\processor\src\protocols;

use dostoevskiy\processor\src\classes\AbstractTask;
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
                $resp = json_encode(['status' => 'error', 'error' => 'Task directive is missing']);
                $length = strlen($resp);
                $connection->send("HTTP/1.1 400 Bad Request\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                $connection->close();

                return;
            }
            if (!$taskData) {
                $resp = json_encode(['status' => 'error', 'error' => 'Task data is missing']);
                $length = strlen($resp);
                $connection->send("HTTP/1.1 400 Bad Request\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                $connection->close();

                return;
            }
            /** @var AbstractTask $taskInstance */
            $taskInstance = ArrayHelper::getValue($tasks, $taskName);
            if (!$taskInstance) {
                $resp = json_encode(['status' => 'error', 'error' => "Unknown task $taskName"]);
                $length = strlen($resp);
                $connection->send("HTTP/1.1 404 Not Found\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                $connection->close();

                return;
            }
            $resp   = json_encode(['status' => 'success']);
            $length = strlen($resp);
            if ($taskInstance->isLive()) {
                if ($taskInstance->isTransactional()) {
                    $taskInstance->prepare($taskData);
                    $resp   = json_encode($taskInstance->process($taskData));
                    $length = strlen($resp);
                    $connection->send("HTTP/1.1 200 OK\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();

                    return;
                } else {
                    $connection->send("HTTP/1.1 200 OK\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();
                    $taskInstance->prepare($taskData);
                    $taskInstance->process($data);

                    return;
                }
            } else {
                $storageInstance = $taskInstance->storage;
                if ($taskInstance->isTransactional()) {
                    $storageInstance->push($taskName, $taskData);
                    $connection->send("HTTP/1.1 200 OK\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nConnection: keep-alive\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();


                    return;
                } else {
                    $connection->send("HTTP/1.1 200 OK\r\nServer: SmartTaskProcessor\r\nContent-Length: $length\r\nConnection: keep-alive\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();
                    $storageInstance->push($taskName, $taskData);

                    return;
                }
            }
        };
    }
}
