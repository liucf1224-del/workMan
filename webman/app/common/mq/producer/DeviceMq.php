<?php

namespace app\common\mq\producer;

use app\common\mq\MqHelper;
use support\Log;

class DeviceMq
{

    /**
     * 写入云账号日志业务
     * @param $dataMsg
     * @return void
     * @throws \Exception
     */
    public function logInsert($dataMsg)
    {
        Log::info('写入云账号日志业务开始');
        $params = MqHelper::getDefaultParams('device_login_log');
        $connectConfig = MqHelper::getDefaultConnectConfig();
        try {
            $conn = MqHelper::createConnection($connectConfig);
            $channel = MqHelper::createChannel($conn);
            $exchange = MqHelper::createExchange($channel, $params['exchangeName']);
            $queue = MqHelper::createQueue($channel, $params['queueName']);
            MqHelper::bindQueue($queue, $params['exchangeName'], $params['routeKey']);

            $message = json_encode($dataMsg, JSON_UNESCAPED_UNICODE);
            $exchange->publish(
                $message,
                $params['routeKey']
            );

            $conn->disconnect();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}