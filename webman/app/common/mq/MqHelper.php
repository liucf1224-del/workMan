<?php

namespace app\common\mq;

class MqHelper
{
    /**
     * 获取默认连接配置
     * @return array
     */
    public static function getDefaultConnectConfig()
    {
        return [
            'host' => getenv('RABBITMQ_HOST'),
            'port' => getenv('RABBITMQ_PORT'),
            'login' => getenv('RABBITMQ_LOGIN'),
            'password' => getenv('RABBITMQ_PASSWORD'),
            'vhost' => getenv('RABBITMQ_VHOST'),
        ];
    }

    /**
     * 获取默认参数配置
     * @param string $name
     * @return array
     */
    public static function getDefaultParams($name)
    {
        return [
            'exchangeName' => $name,
            'queueName' => $name,
            'routeKey' => $name,
        ];
    }

    /**
     * 建立AMQP连接
     * @param array $connectConfig
     * @return \AMQPConnection
     * @throws \Exception
     */
    public static function createConnection($connectConfig)
    {
        $conn = new \AMQPConnection($connectConfig);
        $conn->connect();
        
        if (!$conn->isConnected()) {
            throw new \Exception('RabbitMQ connection failed: ' . json_encode($connectConfig));
        }
        
        return $conn;
    }

    /**
     * 创建通道
     * @param \AMQPConnection $conn
     * @return \AMQPChannel
     * @throws \Exception
     */
    public static function createChannel($conn)
    {
        $channel = new \AMQPChannel($conn);
        
        if (!$channel->isConnected()) {
            throw new \Exception('RabbitMQ channel connection failed');
        }
        
        return $channel;
    }

    /**
     * 创建并配置交换机
     * @param \AMQPChannel $channel
     * @param string $exchangeName
     * @param string $type
     * @return \AMQPExchange
     */
    public static function createExchange($channel, $exchangeName, $type = 'direct')
    {
        $exchange = new \AMQPExchange($channel);
        $exchange->setName($exchangeName);
        $exchange->setType($type);
        $exchange->declareExchange();

        // 为单条消息设置过期时间
//        $exchange->publish($message, $routeKey, AMQP_NOPARAM, [
//            'expiration' => '60000'
//        ]);
        // 发送高优先级消息
//        $exchange->publish($message, $routeKey, AMQP_NOPARAM, [
//            'priority' => 5 // 优先级0-10
//        ]);

        // 消息持久化
//        $exchange->publish($message, $routeKey, AMQP_NOPARAM, [
//            'delivery_mode' => 2 // 持久化消息
//        ]);

        return $exchange;
    }

    /**
     * 创建并配置队列
     * @param \AMQPChannel $channel
     * @param string $queueName
     * @param array $arguments
     * @return \AMQPQueue
     */
    public static function createQueue($channel, $queueName, $arguments = [])
    {
        $queue = new \AMQPQueue($channel);
        $queue->setName($queueName);
        $queue->setFlags(AMQP_DURABLE);
        
        // 设置队列参数（如死信队列、TTL等）
        foreach ($arguments as $key => $value) {
            $queue->setArgument($key, $value);
            // 队列持久化
//            $queue->setFlags(AMQP_DURABLE);
            // 为整个队列设置消息过期时间
//            $queue->setArgument('x-message-ttl', 60000); // 60秒
            // 声明优先级队列
//            $queue->setArgument('x-max-priority', 10);
        }
        
        $queue->declareQueue();
        
        return $queue;
    }

    /**
     * 绑定队列到交换机
     * @param \AMQPQueue $queue
     * @param string $exchangeName
     * @param string $routeKey
     * @return void
     */
    public static function bindQueue($queue, $exchangeName, $routeKey)
    {
        $queue->bind($exchangeName, $routeKey);
    }
}