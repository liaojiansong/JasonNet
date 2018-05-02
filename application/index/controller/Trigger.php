<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/4/24
 * 时间: 21:21
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\TriggerModel;
use function request;
use think\Session;

class Trigger extends BaseController
{
    public function index()
    {
        $list = TriggerModel::with('device')->where('product_id',Session::get('product_id'))->paginate(6);
        $this->assign([
            'flag' => $this->request->param('flag') ?? null,
            'list' => $list,
        ]);
        return $this->fetch('trigger-list');
    }

    public function create()
    {
        $param = $this->request->param();
        $this->assign([
            'device_id' => $param['device_id'] ?? null,
        ]);
        return $this->fetch('trigger-edit');
    }

    public function store()
    {
        $param = $this->request->param();
        $param['product_id'] = Session::get('product_id');
        $flag = $this->validate($param, 'CommonValidate.add_trigger');
        // 验证成功
        if ($flag === true) {
            $res = TriggerModel::newCreate($param);
            if ($res) {
                TriggerModel::addTargetIntoRedis($param['device_id'], $param);
                $this->redirect('index', ['flag' => 'create_success']);
            } else {
                $this->error('添加触发器失败');
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
        $id = $this->request->param('id');
        $one = TriggerModel::get($id)->hidden(['create_time', 'update_time'])->toJson();
        $this->assign([
            'one' => $one,
            'action' => $this->request->action(),
        ]);
        return $this->fetch('trigger-edit');
    }

    public function update()
    {
        $param = request()->param();
        $flag = $this->validate($param, 'CommonValidate.add_trigger');
        if ($flag === true) {
            $device = new TriggerModel();
            $device->newUpdate($param['id'], $param);
            $this->redirect('index', ['flag' => 'update_success']);
        } else {
            $this->error($flag);
        }

    }
    public function delete()
    {
        $id = $this->request->post('id');
        TriggerModel::destroy($id);
        return self::ajaxMsg();
    }


}