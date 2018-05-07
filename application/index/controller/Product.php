<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/23
 * Time: 21:31
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\DevicesModel;
use app\index\model\ProductIndustryModel;
use app\index\model\ProductModel;
use think\Db;
use think\Session;

class Product extends BaseController
{
    public function index()
    {
//        request()->has('product_id') ? Session::set('product_id', request()->param('product_id')) : null;
        $product_id = Session::get('product_id');
        $one = ProductModel::getList($product_id);
        $this->assign([
            'flag' => $this->request->param('flag') ?? null,
            'one' => $one[0] ?? null,
        ]);
        return $this->fetch('product-index');
    }
    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $product_id = $this->request->param('product_id');
        request()->has('product_id') ? Session::set('product_id', request()->param('product_id')) : null;
        $one = ProductModel::get($product_id)->hidden(['create_time', 'update_time'])->toJson();
        $options = ProductIndustryModel::select_option();
        $this->assign([
            'one'     => $one,
            'action'  => $this->request->action(),
            'options'  => $options,
        ]);
        return $this->fetch('product-edit');
    }

    public function update()
    {
        $param = request()->param();
        $flag = $this->validate($param, 'CommonValidate.add_product');
        if ($flag === true) {
            $device = new ProductModel();
            $device->newUpdate($param['id'], $param);
            $this->redirect('index', ['flag' => 'update_success']);
        } else {
            $this->error('编辑失败');
        }
    }

    public function delete()
    {
        $id = $this->request->param('id');
        Db::transaction(function () use ($id) {
            ProductModel::destroy($id);
            DevicesModel::destroy(['product_id' => $id]);
        });
        return self::ajaxMsg();
    }



}