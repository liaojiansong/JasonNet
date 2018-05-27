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
    public function tryToResponse(stdClass $payload, $result)
    {
        $device_id = $payload->device_id ?? false;
        $response_topic = $payload->response_topic ?? false;
        if ($device_id && $response_topic) {
            $this->publishResponse($response_topic, $device_id, $result);
        }
    }

    /**
     * 生成鉴权信息
     * @return array
     */
    public static function getAuthInfo()
    {
        $auth_info = [
            '20189598' => [
                'api_key' => 'ODQ2MC42NDA1MDEwNDE1ODMxMjg=',
                'devices' => [
                    '96545' => 'temperature_A_01',
                    '96546' => 'humidity_B_01',
                    '96548' => 'light_level_B_01',
                    '96549' => 'CO2_D_01',
                    '96550' => 'mould_E_01',
                    '96551' => 'CO2_D_02',
                    '96552' => 'humidity_B_02',
                    '96553' => 'temperature_A_02',
                ]
            ],
            '20189657' => [
                'api_key' => 'MTUyNTU3NDI3MzY2MTUw',
                'devices' => [
                    '96554' => 'temperature_no_1',
                    '96555' => 'weigth',
                    '96556' => 'water',
                    '96557' => 'fresh',
                    '96558' => 'health',
                    '96559' => 'cramarm',
                    '96660' => 'water',
                    '96661' => 'car',
                    '96662' => 'nicvre',
                    '96663' => 'bed_light',
                    '96664' => 'mi_seven',
                    '96665' => 'dianfanbao',
                    '96666' => 'ledlight',
                    '96667' => 'mi_6x',
                    '96668' => 'fang',
                    '96669' => 'hot',
                    '96670' => 'light',
                ]
            ]
        ];
        $product_id = array_rand($auth_info);
        $api_key = $auth_info[$product_id]['api_key'];
        $devices = $auth_info[$product_id]['devices'];
        $device_id = array_rand($devices);
        $device_auth = $devices[$device_id];
        return [
            'product_id' => $product_id,
            'api_key' => $api_key,
            'device_id' => $device_id,
            'device_auth' => $device_auth,
        ];
    }

    /**
     * 返回一个设备id
     * @return mixed
     */
    public static function getDeviceId()
    {
        $device_ids = [
            '96545' => '96545',
            '96546' => '96546',
            '96548' => '96548',
            '96549' => '96549',
            '96550' => '96550',
            '96551' => '96551',
            '96552' => '96552',
            '96553' => '96553',
            '96554' => '96554',
            '96555' => '96555',
            '96556' => '96556',
            '96557' => '96557',
            '96558' => '96558',
            '96559' => '96559',
            '96660' => '96660',
            '96661' => '96661',
            '96662' => '96662',
            '96663' => '96663',
            '96664' => '96664',
            '96665' => '96665',
            '96666' => '96666',
            '96667' => '96667',
            '96668' => '96668',
            '96669' => '96669',
            '96670' => '96670',
        ];
        return array_rand($device_ids);
    }
}