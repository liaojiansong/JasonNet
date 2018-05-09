<?php
/**
 * 服务器脚本基类
 * @User: Jason
 * @Date: 2018/4/26
 */
require_once('MysqliDb.php');
class Base
{
    protected $mysql = null;
    protected $redis = null;
    protected $mqtt = null;
    // 白名单前缀
    const DEVICE_WHITE_LIST = 'device_white_list_';
    // 报警列表
    const REPORT_LIST = 'report_list';
    // 设备数据栈前缀
    const DATA_LIST = "data_list_";
    // 触发器名称前缀
    const TARGET = 'target_';


    const expire_time = 600;
    const host = '127.0.0.1';
    const device_data_table = 'device_data';
    const device_table = 'devices';
    const device_log = 'device_log';
    const product_table = 'products';
    const device_info = [
        1=>'mi_seven',
        2=>'dianfanbao',
        3=>'ledlight',
        4=>'cramarm',
        5=>'water',
        6=>'car',
        7=>'nicvre',
        8=>'water',
        9=>'fresh',
        10=>'health',
        11=>'weigth',
        12=>'mi_6x',
        13=>'fang',
        14=>'hot',
        15=>'light',
        16=>'bed_light',
    ];

    /**
     * 初始化连接
     * Base constructor.
     * @param bool $use_mysql
     * @param bool $use_redis
     * @param bool $use_mqtt
     */
    public function __construct($use_mysql = true,$use_redis=true,$use_mqtt=true)
    {
        if ($use_mysql) {
            $db = new MysqliDb(self::host, 'root', 'liao325339', 'jasonnet');
            $this->mysql = $db;
        }
        if ($use_redis) {
            $redis = new Redis();
            $redis->connect(self::host);
            $this->redis = $redis;
        }
        if ($use_mqtt) {
            $mqtt = new Mosquitto\Client();
            $mqtt->connect(self::host, 1883, 50);
            $this->mqtt = $mqtt;
        }

    }

    /**
     * 日志
     * @param $device_id
     * @param $record_type
     * @param $content
     */
    public function Log($device_id,$record_type,$content)
    {
        /**
         * 日志类型,int=>登入,out=>注销,send_order=>发送命令,add_trigger=>添加触发器，del_trigger=>删除触发器
         */
        $data = [
            'device_id' =>$device_id,
            'record_type'=> $record_type,
            'content'=> $content,
            'create_time'=> time(),
            'update_time'=> time(),
        ];
        $this->mysql->insert(self::device_log, $data);
    }

    /**
     * 发送响应信息
     * @param $pub_topic
     * 主题
     * @param $device_id
     * 设备id
     * @param string $result
     * 信息
     */
    public function publishResponse($pub_topic, $device_id, $result = 'failure')
    {
        $payload = [
            'device_id' => $device_id,
            'result' => $result,
        ];
        $this->mqtt->publish($pub_topic, json_encode($payload), 1, 0);
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

    /**
     * 尝试着响应
     * @param object $payload
     * 解码后的消息体
     * @param $result
     * 返回的信息
     */
    public function tryToResponse(object $payload, $result)
    {
        $device_id = $payload->device_id ?? false;
        $response_topic = $payload->response_topic ?? false;
        if ($device_id && $response_topic) {
            $this->publishResponse($response_topic, $device_id, $result);
        }
    }

}