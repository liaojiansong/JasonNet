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
        while (true) {
            $this->mqtt->loop();
            $rand = rand(1, 12);
            $payload = [
                'device_id' => $rand,
                'data_type' => 'auth',
                'data_content' => self::device_info[$rand],
                'create_time' => time(),
                'update_time' => time(),
            ];
            $mid = $this->mqtt->publish($pub_topic, json_encode($payload), 1, 0);
            echo "当前发送鉴权信息 ID: {$mid}\n";
            $this->mqtt->loop();
            sleep(30);
        }
        $this->mqtt->disconnect();

        unset($client);
    }
}

$obj = new AuthPublish();
$obj->foreverPublish();