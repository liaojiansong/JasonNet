<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);
require_once('MysqliDb.php');

use GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static $redis=null;
    public static $mysql=null;
    const device_data_table = 'device_data';

    public static function getRedis()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        self::$redis = $redis;

    }

    public static function getMysql()
    {
        $db = new MysqliDb('127.0.0.1', 'root', 'liao325339', 'jasonnet');
        self::$mysql = $db;
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        if (!(self::$redis instanceof Redis)) {
            self::getRedis();
        }
        if (!(self::$mysql instanceof MysqliDb)) {
            self::getMysql();
        }
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
       $json_obj = json_decode($message);
       echo "----------------------------------以下是发过来的信息---------------------\n";
//       var_dump($json_obj);
       $type =  $json_obj->type ?? null;
//       print_r($json_obj->content);
       if (!empty($type)) {
           switch ($type) {
               case 'send_ids':
                   $info = self::getOnlineInfo($json_obj->content);
                   self::buildMsg($client_id, 'online_info', $info);
                   break;
               case 'get_device_data':
                   $info = self::getNewDataInfo($json_obj->content->device_id ?? null);
                   self::buildMsg($client_id, 'new_device_data', $info);
           }

       }

   }

    public static function buildMsg($client_id, $type,$message)
    {
        $body = [
            'type' => $type,
            'content' => $message,
        ];
        Gateway::sendToClient($client_id, json_encode($body));
    }

    public static function getOnlineInfo($ids)
    {
        $info = [];
        $redis = self::$redis;
        $redis->connect('127.0.0.1');
        foreach ($ids as $value) {
            if ($redis->exists('device_white_list_' . $value)) {
                $info[$value] = 'online';
            } else {
                $info[$value] = 'outline';
            }
        }
        return $info;
    }

    public static function getNewDataInfo($device_id)
    {

        $mysql = self::$mysql;
        $mysql->where('device_id', $device_id)->orderBy('create_time');
        $new_data = $mysql->get(self::device_data_table, 1);
        return $new_data;


    }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
   }
}
