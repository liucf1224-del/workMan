<?php
namespace app\process;

use app\common\mq\producer\DeviceMq;
use support\Log;

class UdpLogin
{
    public function onMessage($connection, $data)
    {
        $logFile = base_path() . '/logs/udp_debug.log';
        $now = date('Y-m-d H:i:s');
        file_put_contents($logFile, "$now - $data\n", FILE_APPEND);

        $format = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($format)) {
            $error_reply = [
                'code' => 400,
                'msg' => 'JSON格式错误'
            ];
            $connection->send(json_encode($error_reply, JSON_UNESCAPED_UNICODE));
            return;
        }

        $time = time();
        $insert = [
            'account' => (string)($format['account'] ?? ''),
            'time' => $time,
            'create_time' => $time
        ];

        try {
            (new DeviceMq())->logInsert($insert);
            $connection->send(json_encode(['code' => 200, 'msg' => '入队成功'], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::error('UDP->MQ发送失败: '.$e->getMessage(), ['line' => $e->getLine()]);
            file_put_contents($logFile, "$now - MQ错误: {$e->getMessage()} line:{$e->getLine()}\n", FILE_APPEND);
            $connection->send(json_encode(['code' => 500, 'msg' => 'MQ发送失败'], JSON_UNESCAPED_UNICODE));
        }
    }
}
