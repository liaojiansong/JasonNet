<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/5/1
 * 时间: 11:15
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\ProductIndustryModel;
use app\index\model\ProductModel;
use think\Session;

class Index extends BaseController
{
    public function index()
    {
        $list = ProductModel::getList();
        $this->assign([
            'list' => $list,
        ]);
        return $this->fetch('product-index');
    }

    public function create_product()
    {
        $options = ProductIndustryModel::select_option();
        $this->assign([
            'action' => 'create',
            'options' => $options,
        ]);
        return $this->fetch('product-create');
    }

    public function store()
    {
        $param = $this->request->param();
        $flag = $this->validate($param, 'CommonValidate.add_product');
        // 验证成功
        if ($flag === true) {
            $param['user_id'] = Session::get('user_info')['id'];
            $res = ProductModel::newCreate($param);
            if ($res) {
                $this->redirect('index', ['flag' => 'create_success']);
            } else {
                $this->error('添加产品失败');
            }
            // 验证失败
        } else {
            $this->error($flag);
        }
    }


}