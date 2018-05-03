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
    const DEVICE_WHITE_LIST = 'device_white_list_';


    const expire_time = 600;
    const host = '127.0.0.1';
    const device_data_table = 'device_data';
    const device_table = 'devices';
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
    ];

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

}