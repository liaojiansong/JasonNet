<?php
/**
 * 触发器模型
 * @User: Jason
 * @Date: 2018/4/26
 */

namespace app\index\model;


use app\common\BaseModel;
use Redis;

class TriggerModel extends BaseModel
{
    protected $table = 'triggers';
    // 可写入字段
    protected static $fillable = ['device_id','trigger_name','target_condition', 'target_type','trigger_value','phone_check', 'phone','email_check','email','report_msg',];

    public static function newCreate($param, $fillable = null)
    {
        return parent::newCreate($param, self::$fillable);
    }

    public function newUpdate($id, $param)
    {
        $instance = new self();
        $instance->allowField(self::$fillable)->save($param, ['id' => $id]);
    }

    public function deviceData()
    {
        return $this->hasMany('DeviceDataMode', 'devices_id');
    }

    public function device()
    {
        return $this->belongsTo('DevicesModel', 'device_id');
    }
    public static function getRedis($host = '127.0.0.1')
    {
        $redis = new Redis();
        $redis->connect($host);
        return $redis;
    }

    /**
     * 将报警信息写入redis
     * @param $device_id
     * 设备id
     * @param $report_type
     * 报警方式
     * @param $target_condition
     * 报警条件
     * @param $target_value
     * 阈值
     * @return bool
     */
    // TODO 新增一条触发器以后将其写入redis
    public static function addTargetIntoRedis($device_id, $trigger_info)
    {
        $redis = self::getRedis();
        $res = $redis->hMset('target_' . $device_id, $trigger_info);
        $redis->close();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteTrigger($device_id)
    {
        $redis = self::getRedis();
        $res = $redis->del('target_' . $device_id);
        $redis->close();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

}