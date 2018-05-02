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

class Product extends BaseController
{
    public function index()
    {
        $this->assign([
            'flag' => $this->request->param('flag') ?? null,
        ]);
        return $this->fetch('product-index');
    }



    public function create()
    {
        $options = ProductIndustryModel::select_option();
        $this->assign([
            'action' => $this->request->action(),
            'options' => $options,
        ]);
        return $this->fetch('product-edit');
    }

    public function store()
    {
        $flag = $this->validate($this->request->param(), 'CommonValidate.add_product');
        // 验证成功
        if ($flag === true) {
            $res = ProductModel::newCreate($this->request->param());
            if ($res) {
                $this->redirect('index',['flag'  => 'create_success']);
            } else {
                $this->error('添加产品失败');
            }
            // 验证失败
        } else {
            $this->error($flag);
        }
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $product_id = $this->request->param('product_id');
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