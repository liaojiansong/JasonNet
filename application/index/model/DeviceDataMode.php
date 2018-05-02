<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 22:42
 */

namespace app\index\model;


use app\common\BaseModel;

class DeviceDataMode extends BaseModel
{
    protected $table = 'device_data';

    /**
     * 获取数据总数,昨日数据,近7日数据总数
     * @param $id
     * 设备id
     * @return array
     * 统计数组
     */
    public static function getCount($id)
    {
        // TODO 虚拟机时间不对
        // 总数
        $total = self::where('devices_id', $id)->count();
        // 近7天
        $last_week = self::where('devices_id', $id)->whereTime('create_time', 'week')->count() ;
        // 昨天
        $yesterday = self::where('devices_id', $id)->whereTime('create_time', 'yesterday')->count();
        return [
            'total' => $total,
            'last_week' => $last_week,
            'yesterday' => $yesterday,
        ];
    }
}