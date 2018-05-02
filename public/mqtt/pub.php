<?php
/**
 * 发布器
 * User: Administrator
 * Date: 2018/4/21
 * Time: 11:51
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
$client = new Mosquitto\Client();
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onSubscribe('subscribe');
$client->onMessage('message');
$client->connect("127.0.0.1", 1883, 50);
$client->subscribe('order', 1);

while (true) {
    $client->loop();
    $pub_topic = 'data';
    $payload = [
        'devices_id' => rand(1, 11),
        'data_type' => rand(0, 4),
        'data_content' => rand(15, 50),
        'create_time' => time(),
        'update_time' => time(),
    ];
    $mid = $client->publish($pub_topic, json_encode($payload), 1, 0);
    echo "当前发送消息 ID: {$mid}\n";
    $client->loop();
    sleep(15);
}
$client->disconnect();

unset($client);
function connect($r)
{
    echo "我是连接成功时的回调函数,服务器响应代码为{$r}\n";
}

function subscribe()
{
    echo "服务器了我的响应订阅请求,所以我被调用了\n";
}

function message($message)
{
    printf("收到一条消息 %d 来自主体 %s 消息体是:\n%s\n\n", $message->mid, $message->topic, $message->payload);
}

function disconnect($rc)
{
    echo "哎呀,连接断开了,服务器响应代码为{$rc}\n";
}