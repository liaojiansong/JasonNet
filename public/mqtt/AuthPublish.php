<?php
/**
 * 权限发送器
 * @User: Jason
 * @Date: 2018/5/3
 */
require_once('Base.php');
class AuthPublish extends Base
{
    public function __construct()
    {
        parent::__construct(false, false, true);

    }

    public function foreverPublish($pub_topic = 'auth')
    {
        /**
         * // api_key(必填)
         * "api_key": "MTUyNTU3NDI3MzY2MTUw",
         * // 产品id(必填)
         * "product_id": 1,
         * // 设备id(必填)
         * "device_id": "20181516",
         * // 消息类型
         * "data_type": "auth",
         * // 鉴权信息(必填)
         * "device_auth": "device_mi7",
         * // 接收鉴权的主题(必填)
         * "response_topic":"response_1"
         */
        while (true) {
            $this->mqtt->loop();
            $product_id = rand(1, 3);
            $auth_box = self::device_info[$product_id];
            $device_id = array_rand($auth_box);

            $payload = [
                'api_key'=>self::api_key[$product_id],
                'product_id'=> $product_id,
                'device_id' => $device_id,
                'data_type' => 'auth',
                'device_auth' => $auth_box[$device_id],
                'response_topic' => 'response_1',
            ];
            $mid = $this->mqtt->publish($pub_topic, json_encode($payload), 1, 0);
//            echo "当前发送鉴权信息 ID: {$mid}\n";
//            echo '-----------------------发送的鉴权消息为-------------------------------------';
//            echo "\n";
//            var_dump($payload);
//            echo "\n";

            $this->mqtt->loop();
            sleep(30);
        }
        $this->mqtt->disconnect();

        unset($client);
    }
}

$obj = new AuthPublish();
$obj->foreverPublish();