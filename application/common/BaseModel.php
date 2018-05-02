<?php
/**
 * Created by PhpStorm.
 * UserModel: Administrator
 * Date: 2018/4/8
 * Time: 22:19
 */

namespace app\common;


use think\Model;

class BaseModel extends Model
{
    protected static $fillable = null;

    /**
     * 新的创建方法
     * @param $param
     * 传入的变量
     * @return bool|mixed
     */
    public static function newCreate($param, $fillable = null)
    {
        $user = self::create($param, $fillable);
        if (isset($user->id)) {
            return $user->id;
        } else {
            return false;
        }
    }


}