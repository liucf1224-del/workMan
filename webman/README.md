# WebMan 项目文档

## 项目概述

本项目基于 WebMan 框架构建，主要用于处理设备登录日志数据。系统采用 UDP 协议接收设备登录信息，并通过 RabbitMQ 消息队列进行异步处理，最终将数据存储到数据仓库中。

## 核心组件

### 1. UDP 接收服务

#### 文件位置
- [app/process/UdpLogin.php](./app/process/UdpLogin.php)

#### 功能说明
- 监听 UDP 端口 `0.0.0.0:2349` 接收设备发送的 JSON 格式登录数据
- 解析接收到的 JSON 数据，验证其格式正确性
- 将解析后的数据包装成指定格式后发送至消息队列
- 发送响应给客户端，通知处理结果

#### 处理流程
1. 接收 UDP 数据包
2. 验证 JSON 格式
3. 提取关键字段(account, time等)
4. 调用消息队列发送数据
5. 返回处理结果给客户端

### 2. 消息队列(MQ) 生产者

#### 文件位置
- [app/common/mq/producer/DeviceMq.php](./app/common/mq/producer/DeviceMq.php)

#### 功能说明
- 负责将设备登录日志数据发送到 RabbitMQ 消息队列
- 使用 `device_login_log` 作为交换机、队列和路由键名称
- 支持连接池管理和异常处理

#### 主要方法
- `logInsert($dataMsg)` - 将设备登录日志数据插入消息队列

### 3. 消息队列(MQ) 消费者

#### 文件位置
- [app/process/consumer/DeviceLogMq.php](./app/process/consumer/DeviceLogMq.php)

#### 功能说明
- 从 RabbitMQ 消费设备登录日志数据
- 将数据持久化保存到数据库中
- 实现了手动 ACK 应答机制确保消息可靠处理

#### 主要方法
- `onWorkerStart()` - 初始化消费者并开始监听消息队列
- `processMessage($data)` - 处理接收到的消息并保存到数据库

### 4. 消息队列助手类

#### 文件位置
- [app/common/mq/MqHelper.php](./app/common/mq/MqHelper.php)

#### 功能说明
- 提供 RabbitMQ 连接、通道、交换机、队列等基础操作的封装
- 支持连接配置管理、资源创建和绑定等操作
- 统一处理 AMQP 相关对象的创建和配置

### 5. WebSocket 服务

#### 文件位置
- [plugin/webman/gateway/Events.php](./plugin/webman/gateway/Events.php)
- [config/plugin/webman/gateway-worker/process.php](./config/plugin/webman/gateway-worker/process.php)

#### 功能说明
- 提供 WebSocket 服务，监听端口 `0.0.0.0:7272`
- 实现客户端连接、消息接收与发送、连接断开等事件处理
- 配置心跳检测机制，防止连接超时断开

#### 配置详情
- WebSocket 监听地址：`websocket://0.0.0.0:7272`
- 心跳间隔：25秒
- 心跳数据：`{"type":"ping"}`
- 注册中心地址：`127.0.0.1:1236`
- 业务逻辑处理器：`plugin\webman\gateway\Events::class`

#### 主要事件处理函数
- `onWorkerStart($worker)` - 工作进程启动时的初始化操作
- `onConnect($client_id)` - 客户端连接时触发
- `onWebSocketConnect($client_id, $data)` - WebSocket 连接建立时触发
- `onMessage($client_id, $message)` - 接收客户端消息时触发，当前实现为回显消息
- `onClose($client_id)` - 客户端断开连接时触发

## 数据流向

```
设备 -> UDP协议 -> UdpLogin进程 -> RabbitMQ -> DeviceLogMq消费者 -> 数据库
```

1. 设备通过 UDP 协议发送登录数据到服务器
2. [UdpLogin](./app/process/UdpLogin.php) 进程接收数据并验证格式
3. 验证通过后调用 [DeviceMq](./app/common/mq/producer/DeviceMq.php) 类将数据发送到消息队列
4. [DeviceLogMq](./app/process/consumer/DeviceLogMq.php) 消费者从队列中取出数据
5. 将数据持久化保存到 [DeviceLoginLogModel](./app/model/warehouse/DeviceLoginLogModel.php) 对应的数据库表

## 配置文件

### 进程配置
- [config/process.php](./config/process.php) - 定义了 UDP 登录进程和其他进程的配置

### 数据库配置
- [config/database.php](./config/database.php) - 包含多个数据库连接配置，包括数据仓库连接

### 日志配置
- [config/log.php](./config/log.php) - 配置了专门的日志处理器用于设备登录日志记录

## 环境变量

项目使用以下环境变量配置 RabbitMQ 连接：

- `RABBITMQ_HOST` - RabbitMQ 服务器地址
- `RABBITMQ_PORT` - RabbitMQ 端口号
- `RABBITMQ_LOGIN` - 用户名
- `RABBITMQ_PASSWORD` - 密码
- `RABBITMQ_VHOST` - 虚拟主机

数据仓库数据库相关环境变量：
- `DB_HOST_WAREHOUSE`, `DB_PORT_WAREHOUSE`, `DB_NAME_WAREHOUSE`, `DB_USER_WAREHOUSE`, `DB_PASSWORD_WAREHOUSE` - 数据仓库数据库连接信息

## 启动命令

项目支持通过以下命令启动：

- Linux: `php start.php start`
- Windows: `php windows.php start` 或执行 `windows.bat`

## 注意事项

1. 确保 RabbitMQ 服务正在运行且网络可达
2. 确保数据库连接配置正确
3. UDP 端口 2349 需要在防火墙中开放
4. 项目中的日志记录功能会在 `logs` 目录下生成相应日志文件

## 调试的时候 线上和线下可以使用的调试方式
1.先可以线上去调试一下，对应的命令
sudo ss -ulnp | grep :19234 这个是看你的udp端口是否是开放的

sudo tcpdump -i any udp port 19234 -vv -nn  这个是看这个端口的包是不是发过来了

echo '{"account": "test_permission"}' | nc -u -w 2 127.0.0.1 2349 线上可以这样校验

本地需要安装一个这个 Nmap - Zenmap GUI 就可以用这个命令来玩
下载地址 
[网址](https://nmap.org/download.html)
 选windows，安装在c或者d都可以
# 在你的Windows命令提示符或PowerShell中执行（确保已安装nmap的ncat）
echo {"account": "final_remote_test"} | ncat -u -w 3 136.11.16.10 2349
