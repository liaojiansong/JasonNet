<?php
/**
 * 订阅数据信息信息
 * @User: Jason
 * Date: 2018/4/21
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class SubDataTopic extends Base
{

    public function __construct()
    {
        parent::__construct(false,true,true);

    }

    /**
     * 检测设备是否在白名单
     * @param $device_id
     * @return bool
     */
    public function existsWhiteList($device_id)
    {
        $white_list_name = self::DEVICE_WHITE_LIST . $device_id;
        $flag = $this->redis->exists($white_list_name);
        return $flag;
    }

    public function resetExpire($device_id)
    {
        $white_list_name = self::DEVICE_WHITE_LIST . $device_id;
        $this->redis->expire($white_list_name,self::expire_time);
    }
    /**
     * 订阅并写入redis
     */
    public function sub()
    {
        /**
         * object(Mosquitto\Message)#5 (5) {
         * ["mid"]=>
         * int(6)
         * ["topic"]=>
         * string(4) "data"
         * ["payload"]=>
         * string(99) "{"device_id":11,"data_type":0,"data_content":42,"create_time":1518842548,"update_time":1518842548}"
         * ["qos"]=>
         * int(1)
         * ["retain"]=>
         * bool(false)
         * }
         */
        $this->mqtt->subscribe('data', 1);
        $this->mqtt->onMessage(function ($msg) {
            $payload = json_decode($msg->payload);
            $device_id = $payload->device_id;
            // 如果在白名单就存入redis
            if ($this->existsWhiteList($device_id)) {
//                echo '-----------------------数据-------------------------------------';
//                echo "\n";
//                var_dump($msg);
//                echo "\n";
                // 重置白名单存活时间
                $this->resetExpire($device_id);
                $list_name = self::DATA_LIST.$device_id;
                $this->redis->lPush($list_name, json_encode($msg));
                $this->redis->expire($list_name, 600);
            }
        });
        $this->mqtt->loopForever();
    }
}

$user = new SubDataTopic();
$user->sub();

