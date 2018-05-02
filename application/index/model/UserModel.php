<?php
/**
 * Created by PhpStorm.
 * UserModel: Administrator
 * Date: 2018/4/8
 * Time: 22:19
 */

namespace app\index\model;


use app\common\BaseModel;


class UserModel extends BaseModel
{
    protected $table = 'user';
    // 可写入字段
    protected static $fillable = ['username', 'email', 'phone', 'password'];

    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d', $value);
    }

    public static function newCreate($param, $fillable = null)
    {
        return parent::newCreate($param, self::$fillable);
    }

    public function newUpdate($id, $param)
    {
        $instance = new self();
        $instance->allowField(self::$fillable)->save($param, ['id' => $id]);
    }

    public function products()
    {
        return $this->hasMany('ProductModel', 'user_id');
    }

}