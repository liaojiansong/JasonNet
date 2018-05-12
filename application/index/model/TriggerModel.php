<?php
/**
 * 触发器模型
 * @User: Jason
 * @Date: 2018/4/26
 */

namespace app\index\model;


use app\common\BaseModel;

class TriggerModel extends BaseModel
{
    protected $table = 'triggers';
    // 可写入字段
    protected static $fillable = ['product_id','alias','device_id','trigger_name','target_condition', 'target_type','target_value','phone_check', 'phone','email_check','email','report_msg',];

    public static function CreateWithID($param)
    {
        $obj = new self();
        foreach (self::$fillable as $value) {
            $obj->$value = $param[$value] ?? null;
        }
        $obj->save();
        // 获取自增ID
        return $obj->id;
    }

    /**
     * @param $id
     * @param $param
     */
    public function newUpdate($id, $param)
    {
        $instance = new self();
        $instance->allowField(self::$fillable)->save($param, ['id' => $id]);
    }

    public function deviceData()
    {
        return $this->hasMany('DeviceDataMode', 'device_id');
    }

    public function device()
    {
        return $this->belongsTo('DevicesModel', 'device_id');
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
    public static function addTargetIntoRedis($device_id, $trigger_info)
    {
        // 别名
        $timestamp = $trigger_info['alias'];
        $redis = self::getRedis();
        $res = $redis->hMset('target_' . $device_id.'_'. $timestamp, $trigger_info);
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