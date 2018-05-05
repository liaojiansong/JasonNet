<?php


/**
 * 订阅鉴权信息
 * @User: Jason
 * @Date: 2018/5/3
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class SubAuthTopic extends Base
{
    public function __construct()
    {
        parent::__construct(true,true,true);
    }

    /**
     * 查询鉴权是否通过
     * @param $device_id
     * 设备id
     * @param $device_auth
     * 设备鉴权信息
     * @return array|bool
     */
    public function checkAuth($device_id, $device_auth)
    {
        $this->mysql->where('id', $device_id);
        $this->mysql->where('device_auth', $device_auth);
        // 返回结果集数组
        $results = $this->mysql->get(self::device_table);
        if (!empty($results)) {
            return $device_id;
        } else {
            return false;
        }
    }

    /**
     * 将鉴权通过的设备加入白名单，并设置过期时间为600秒
     * @param $device_id
     */
    public function insertIntoWhiteList($device_id)
    {
        // 如果不在就插入白名单
        // 如果在就更新过期时间
        $white_list_name = self::DEVICE_WHITE_LIST . $device_id;
        if (!$this->existsWhiteList($device_id)) {
            $this->redis->set($white_list_name, true);
        }
        $this->redis->expire($white_list_name, self::expire_time);
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

    public function sub($topic = 'auth')
    {
        $this->mqtt->subscribe($topic, 1);
        $this->mqtt->onMessage(function ($msg) {
//            echo '-----------------------鉴权信息-------------------------------------';
//            echo "\n";
//            var_dump($msg);
//            echo "\n";
            $payload = json_decode($msg->payload);
            if ($payload->data_type == 'auth') {
                // 通过权限检测的设备id
                $device_id = $this->checkAuth($payload->device_id, $payload->data_content);
                $this->insertIntoWhiteList($device_id);
                $this->Log($device_id, 'in', '设备上线');
            }
        });
        $this->mqtt->loopForever();
    }
}

$obj = new SubAuthTopic();
$obj->sub();