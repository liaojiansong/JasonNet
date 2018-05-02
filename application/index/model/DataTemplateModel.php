<?php
/**
 *
 * @User: Jason
 * @Date: 2018/4/27
 */

namespace app\index\model;


use app\common\BaseModel;

class DataTemplateModel extends BaseModel
{
    protected $table = 'data_templates';
    // 可写入字段
    protected static $fillable = [
        'data_template_name',
        'unit_name',
        'unit_symbol',
    ];

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
}