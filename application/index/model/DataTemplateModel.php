<?php
/**
 *
 * @User: Jason
 * @Date: 2018/4/27
 */

namespace app\index\model;


use app\common\BaseModel;
use think\Session;

class DataTemplateModel extends BaseModel
{
    protected $table = 'data_templates';
    // 可写入字段
    protected static $fillable = [
        'data_template_name',
        'unit_name',
        'unit_symbol',
        'product_id',
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
        return $this->hasMany('DeviceDataMode', 'device_id');
    }

    public static function getTemplateOptions()
    {
        $product_id = Session::get('product_id') ?? 0;
        $options = DataTemplateModel::where('product_id', $product_id)->select()->each(function ($item) {
            $item->option = $item->unit_name . '(' . $item->unit_symbol . ')';
            return $item;
        });
        return $options;
    }
}