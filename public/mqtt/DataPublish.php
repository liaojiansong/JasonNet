<?php
/**
 * 发布器
 * @User: Jason
 * @Date: 2018/5/3
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class DataPublish extends Base
{
    public function __construct()
    {
        parent::__construct(false,false,true);
//        $this->init();
    }

    public function init()
    {
        $this->mqtt->onConnect(function ($r) {
            echo "我是连接成功时的回调函数,服务器响应代码为{$r}\n";
        });
        $this->mqtt->onSubscribe(function () {
            echo "服务器了我的响应订阅请求,所以我被调用了\n";
        });
        $this->mqtt->onMessage(function ($message) {
            printf("收到一条消息 %d 来自主体 %s 消息体是:\n%s\n\n", $message->mid, $message->topic, $message->payload);
        });
        $this->mqtt->onDisconnect(function ($rc) {
            echo "哎呀,连接断开了,服务器响应代码为{$rc}\n";
        });
    }

    public function foreverPublish($pub_topic = 'data')
    {
        while (true) {
            $this->mqtt->loop();
            $payload = [
                'device_id' => self::getDeviceId(),
                'data_type' => 'data',
                'data_content' => rand(-11, 90),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $mid = $this->mqtt->publish($pub_topic, json_encode($payload), 1, 0);
//            echo "当前发送数据消息 ID: {$mid}\n";
            $this->mqtt->loop();
            unset($payload);
            sleep(6);
        }
        $this->mqtt->disconnect();

        unset($client);
    }


}

$obj = new DataPublish();
$obj->foreverPublish();