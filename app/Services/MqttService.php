<?php

namespace App\Services;

use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Сервис обмена сообщениями по протоколу mqtt
 *
 * Class MqttService
 * @package App\Services
 */
class MqttService
{
    protected $client_id;
    protected $token;
    protected $mqtt;

    public function __construct(string $server, int $port, string $token)
    {
        $this->client_id    = uniqid();
        $this->token        = $token;
        $this->mqtt         = new phpMQTT($server, $port, $this->client_id);
    }

    /**
     * Отправка запроса в шину
     *
     * @param string $topic
     * @param array $message
     * @return array
     */
    public function publish(string $topic, array $message)
    {
        if ($this->mqtt->connect(true, null, $this->token, '')) {
            $this->mqtt->publish(
                $topic,
                json_encode($message),
                0
            );

            $this->mqtt->close();
        } else {
            Log::info('Connect fail');
        }
    }

    /**
     * Функция реализовывает работы RPC CALL
     *
     * @param array $params
     */
    public function rpc(array $params)
    {
        if ($this->mqtt->connect(true, null, $this->token, '')) {

            $topics = [];

            $topics['v1/#'] = ["qos" => 0, "function" => function($topic, $msg) use ($params) {
                $data = json_decode($msg, true);

                Log::info('Пришло сообщение', $data);

                if (in_array($topic, array_keys($params))) {
                    $className = $params[$topic];
                    $response = App::make($className)->run($data['params'] ?? []);

                    if (empty($data['topicResponse']) === false) {

                        Log::info('Отправляем ответное сообщение в ', [$data['topicResponse'], 'ответ:', $response]);

                        $this->mqtt->publish(
                            $data['topicResponse'],
                            json_encode($response),
                            0
                        );
                    }
                }
            }];

            $this->mqtt->subscribe($topics, 0);

            while($this->mqtt->proc()){}
            $this->mqtt->close();

        } else {
            Log::info('Connect fail');
        }
    }
}