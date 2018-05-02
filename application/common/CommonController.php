<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/5/1
 * 时间: 13:24
 */

namespace app\common;


use think\Session;

class CommonController extends BaseController
{
    public static function session_product_id($product_id)
    {
        Session::set('product_id', $product_id);
        return $product_id;
    }

}