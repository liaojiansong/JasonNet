<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/23
 * Time: 21:09
 */
require_once('MysqliDb.php');
class Test
{
    protected $mysql = null;


    const table = 'device_data';
    const host = '127.0.0.1';

    public function __construct()
    {
        $db = new MysqliDb(self::host, 'root', '', 'jasonnet');
        $this->mysql = $db;
    }

    public function insetIntoTable()
    {

        for ($i = 0; $i < 280; $i++) {
            $payload = [
                'devices_id' => rand(1, 12),
                'data_type' => rand(0, 4),
                'data_content' => rand(10, 50),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $this->mysql->insert(self::table, $payload);
        }


    }

}

$data = new Test();
$data->insetIntoTable();
echo '6666';