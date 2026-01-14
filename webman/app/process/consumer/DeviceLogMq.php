<?php

namespace app\process\consumer;

use app\common\mq\MqHelper;
use app\model\warehouse\DeviceLoginLogModel;
use support\Log;

/**
 * 场所MQ
 */
class DeviceLogMq
{
    public function onWorkerStart()
    {
        //默认配置j
        $params = MqHelper::getDefaultParams('device_login_log');
        //获取连接
        $connectConfig = MqHelper::getDefaultConnectConfig();
        
        try {
            //建立AMQP连接
            $conn = MqHelper::createConnection($connectConfig);
            //创建通道
            $channel = MqHelper::createChannel($conn);
            //配置交换机
            MqHelper::createExchange($channel, $params['exchangeName']);
            //创建队列
            $queue = MqHelper::createQueue($channel, $params['queueName']);
            //绑定队列到交换机
            MqHelper::bindQueue($queue, $params['exchangeName'], $params['routeKey']);
            //监听并且消费
            $queue->consume(function ($message, $queue) {
                $body = json_decode($message->getBody(), true);
                /* @手动ACK应答*/
                $queue->ack($message->getDeliveryTag());

                Log::channel('device_login_log')->info('device_log_insert日志开始消费', $body);
                $this->processMessage($body);
            });
        } catch (\Exception $exception) {
            Log::channel('club_log')->info(
                $exception->getMessage(),
                ['error_line' => $exception->getLine(), 'trace' => $exception->getTraceAsString()]
            );
        }
    }

    /**
     * 消息出来
     * @param $data
     */
    public function processMessage($data)
    {
        Log::info('device_login_log日志数据记录', $data);
        try {
            //写入
            DeviceLoginLogModel::query()->insert($data);
        } catch (\Exception $exception) {
            Log::channel('device_login_log')->info(
                '操作日志处理错误：' . $exception->getMessage(),
                ['error_line' => $exception->getLine(),'trace' => $exception->getTraceAsString()]
            );
        }
    }
}