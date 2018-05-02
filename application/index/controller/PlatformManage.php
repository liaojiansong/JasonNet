<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/5/1
 * 时间: 14:00
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\ProductModel;
use app\index\model\UserModel;

class PlatformManage extends BaseController
{
    public function product_list()
    {
        $list = ProductModel::withCount(['devices'])->with(['user', 'product_industry'])->paginate(5);
        $this->assign([
            'list' => $list,
        ]);
        return $this->fetch('product-list');
    }

    public function user_list()
    {
        $list = UserModel::withCount('products')->paginate(5);
        $this->assign([
            'list' => $list,
        ]);
        return $this->fetch('user-list');
    }

}