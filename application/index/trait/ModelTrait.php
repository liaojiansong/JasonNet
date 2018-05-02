<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/15
 * Time: 15:00
 */

trait ModelTrait
{
    protected static $fillable = null;

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