<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 10:21
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class SubIntoRedis extends Base
{
    private $mqtt = null;
    public function __construct()
    {
        parent::__construct();

        $mqtt = new Mosquitto\Client();
        $mqtt->connect(self::host, 1883, 50);
        $this->mqtt = $mqtt;
    }

    /**
     * 订阅并写入redis
     */
    public function subAndStoreInRedis()
    {
        /**
         * object(Mosquitto\Message)#5 (5) {
         * ["mid"]=>
         * int(6)
         * ["topic"]=>
         * string(4) "data"
         * ["payload"]=>
         * string(99) "{"devices_id":11,"data_type":0,"data_content":42,"create_time":1518842548,"update_time":1518842548}"
         * ["qos"]=>
         * int(1)
         * ["retain"]=>
         * bool(false)
         * }
         */
        $this->mqtt->subscribe('data', 1);
        $this->mqtt->onMessage(function ($msg) {
            var_dump($msg);
            $payload = json_decode($msg->payload);
            $list_name = "data_list_{$payload->devices_id}";
            $this->redis->lPush($list_name, json_encode($msg));
            $this->redis->expire($list_name, 600);
        });
        $this->mqtt->loopForever();
    }
}

$user = new SubIntoRedis();
$user->subAndStoreInRedis();
