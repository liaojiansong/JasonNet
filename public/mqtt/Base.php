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

    const table = 'device_data';
    const host = '127.0.0.1';

    public function __construct()
    {
        $db = new MysqliDb(self::host, 'root', 'liao325339', 'jasonnet');
        $this->mysql = $db;

        $redis = new Redis();
        $redis->connect(self::host);
        $this->redis = $redis;
    }

}