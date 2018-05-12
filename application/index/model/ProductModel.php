<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/4/25
 * 时间: 21:20
 */

namespace app\index\model;


use app\common\BaseModel;
use think\Session;
use function base64_encode;
use function rand;
use function time;

class ProductModel extends BaseModel
{
    protected $table = 'products';
    // 可写入字段
    protected static $fillable = ['product_name', 'product_industry_id', 'product_description','user_id','api_key'];

    public static function newCreate($param, $fillable = null)
    {
        return parent::newCreate($param, self::$fillable);
    }

    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d', $value);
    }

    public function newUpdate($id, $param)
    {
        $instance = new self();
        $instance->allowField(self::$fillable)->save($param, ['id' => $id]);
    }

    // 设备
    public function devices()
    {
        return $this->hasMany('DevicesModel', 'product_id');
    }

    //用户
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    // 产品行业
    public function productIndustry()
    {
        return $this->belongsTo('ProductIndustryModel', 'product_industry_id');
    }

    // 触发器
    public function triggers()
    {
        return $this->hasMany('TriggerModel', 'product_id');
    }

    // 数据流模板
    public function template()
    {
        return $this->hasMany('DataTemplateModel','product_id');
    }

    public static function getList($product_id=null)
    {
        // 获取当前用户下的产品
        $user_info = session('user_info');
        $list = ProductModel::withCount(['devices', 'triggers','template'])->with(['product_industry'])->with([
            'devices' => function ($query) {
                $query->field('product_id,id');
            }
        ])->where('user_id', $user_info['id'])->select($product_id);

        foreach ($list as &$value) {
            $device = $value->devices ?? null;
            $data_total = 0;
            foreach ($device as $val) {
                if ($val !== null) {
                    $device_id = $val->id;
                    $temp = DeviceDataMode::getTotal($device_id);
                    $data_total += $temp;
                }
            }
            $value->data_total = $data_total;

        }
        return $list;
    }

    public static function makeApiKey()
    {
        $user_info = Session::get('user_info');
        $time = time() / $user_info['id'];
        $rand = rand(0, 99999);
        return base64_encode($time . $rand);

    }

}