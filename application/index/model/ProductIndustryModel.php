<?php
/**
 * 产品行业模型
 * @User: Jason
 * @Date: 2018/4/28
 */

namespace app\index\model;


use app\common\BaseModel;

class ProductIndustryModel extends BaseModel
{
    protected $table = 'product_industry';

    public static function select_option()
    {
        return $options = self::all();
    }

}