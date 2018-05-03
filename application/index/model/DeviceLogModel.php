<?php
/**
 *
 * @User: Jason
 * @Date: 2018/5/3
 */

namespace app\index\model;


use app\common\BaseModel;

class DeviceLogModel extends BaseModel
{
    protected $table = 'device_log';

    public static function Log($device_id, $record_type, $content)
    {
        /**
         * 日志类型,int=>登入,out=>注销,send_order=>发送命令,add_trigger=>添加触发器，del_trigger=>删除触发器
         */
        $data = [
            'device_id' => $device_id,
            'record_type' => $record_type,
            'content' => $content,
            'create_time' => time(),
            'update_time' => time(),
        ];
        self::create($data);
    }

    public function device()
    {
        return $this->belongsTo('DevicesModel', 'device_id');
    }

}