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
    const ERROR = [
        1=>'product no match api_key',
        2=>'device no match product_id or device_auth',
    ];
    public function __construct()
    {
        parent::__construct(true,true,true);
    }

    /**
     * 检验信息是否完整
     * @param object $payload
     * @return bool|object
     */
    public function checkPayloadIsComplete(object $payload)
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
        $fields = ['api_key', 'product_id', 'device_id', 'data_type', 'device_auth', 'response_topic'];
        foreach ($fields as $value) {
            if (($payload->$value ?? null) == null) {
                return false;
            }
        }
        return $payload;
    }

    /**
     * 鉴权之前的校验,返回true可以执行鉴权操作
     * @param $payload
     * @return bool
     */
    public function beforeCheckAuth($payload)
    {
        // 如果发送过来的信息不完整,尝试着回应
        if ($this->checkPayloadIsComplete($payload) == false) {
            $this->tryToResponse($payload, 'miss_param');
            return false;
        } else {
            return true;
        }
    }
    /**
     * 查表鉴权,成功返回设备id,失败返回false并直接发送错误信息
     * @param object $payload
     * 解码后的消息体
     * @return mixed
     */
    public function checkAuth(object $payload)
    {
        // 先鉴定product_id 和 api_key是否对应()
        $this->mysql->where('id', $payload->product_id);
        $this->mysql->where('api_key', $payload->api_key);
        // 返回结果集数组
        $res_product = $this->mysql->get(self::product_table);
        if (empty($res_product)) {
            $this->publishResponse($payload->response_topic, $payload->device_id, self::ERROR[1]);
            return false;
        }

        // 再鉴定product_id 和 device 和 device_auth是否匹配
        $this->mysql->where('id', $payload->devcie_id);
        $this->mysql->where('product_id', $payload->product_id);
        $this->mysql->where('device_auth', $payload->device_auth);
        $res_device = $this->mysql->get(self::device_table);
        if (empty($res_device)) {
            $this->publishResponse($payload->response_topic, $payload->device_id, self::ERROR[2]);
            return false;
        }else{
            return $payload->devcie_id;
        }
    }


    /**
     * 将鉴权通过的设备加入白名单，并设置过期时间为600秒
     * @param $device_id
     */
    public function insertIntoWhiteList($device_id)
    {
        // 如果不在就插入白名单
        $white_list_name = self::DEVICE_WHITE_LIST . $device_id;
        if (!$this->existsWhiteList($device_id)) {
            $this->redis->set($white_list_name, true);
            $this->Log($device_id, 'in', '设备上线');
        }
        // 如果在就更新过期时间
        $this->redis->expire($white_list_name, self::expire_time);
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
            if ($this->beforeCheckAuth($payload)) {
                $device_id = $this->checkAuth($payload);
                if ($device_id != false) {
                    $this->insertIntoWhiteList($device_id);
                }
            }
        });
        $this->mqtt->loopForever();
    }
}

$obj = new SubAuthTopic();
$obj->sub();