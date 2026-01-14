<?php
define('GLOBAL_START', 1);
date_default_timezone_set('Asia/Shanghai');
use app\common\mq\producer\DeviceMq;
use Workerman\Worker;
use Workerman\Connection\UdpConnection;

// require_once __DIR__ . '/start.php';
require_once __DIR__ .'/vendor/autoload.php';



// 创建一个Worker监听2347端口，纯UDP协议，不使用任何应用层协议
$udp_worker = new Worker("udp://0.0.0.0:2349");
// 启动4个进程对外提供服务，和你原来的一致
$udp_worker->count = 4;

// 当客户端发来UDP数据时触发（核心业务逻辑区）
$udp_worker->onMessage = function( $connection, $data)
{
    // 1. 直接向终端打印（最原始的信号）
    echo "[" . date('Y-m-d H:i:s') . "] UDP 收到数据: " . $data . "\n";

    // 2. 尝试写入文件 - 使用项目绝对路径，并检查结果
    $logFile = __DIR__ . '/logs/udp_debug.log'; // 修正为项目内的logs目录
    $content = date('Y-m-d H:i:s') . " - " . $data . "\n";
    file_put_contents($logFile, $content, FILE_APPEND);
    //格式校验判断
    $format = json_decode($data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($format)) {
        echo "[" . date('Y-m-d H:i:s') . "] 错误：无效的JSON格式\n";
        // 返回错误信息给客户端
        $error_reply = [
            'code' => 400,
            'msg' => 'JSON格式错误,不要瞎搞'
        ];
        $connection->send(json_encode($error_reply, JSON_UNESCAPED_UNICODE));
        return;
    }
    file_put_contents($logFile, '1111111', FILE_APPEND);
    $time = time();
    file_put_contents($logFile, 'dddd', FILE_APPEND);
        //组装数据 echo '{"account": "959595"}' | ncat -u -w 3 36.111.156.180 2349
       $insert =  [
            'account' =>$format['account']??'',
            'time' => $time,
            'create_time' => $time
        ];
    file_put_contents($logFile, json_encode($insert), FILE_APPEND);
    try {
        (new DeviceMq())->logInsert($insert);
    } catch (\Throwable $e) {
        $err = '[' . date('Y-m-d H:i:s') . '] MQ错误: ' . $e->getMessage() . ' line:' . $e->getLine() . "\n";
        file_put_contents($logFile, $err, FILE_APPEND);
        $connection->send(json_encode(['code'=>500,'msg'=>'MQ发送失败'], JSON_UNESCAPED_UNICODE));
        return;
    }
    file_put_contents($logFile, '222222222', FILE_APPEND);
    // 3. 发送一个简单的响应
    $connection->send('Got: ' . $data);
};

Worker::runAll();
