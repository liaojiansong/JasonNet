<?php
/**
 * Created by PhpStorm.
 * UserModel: Administrator
 * Date: 2018/4/10
 * Time: 22:17
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\UserModel;

class User extends BaseController
{
    public function index()
    {
        return $this->fetch('user-index');
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
        $flag = $this->validate($param, 'CommonValidate.add_trigger');
        // 验证成功
        if ($flag === true) {
            $res = UserModel::newCreate($param);
            if ($res) {
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
        $one = UserModel::get($id)->hidden(['create_time', 'update_time'])->toJson();
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
            $device = new UserModel();
            $device->newUpdate($param['id'], $param);
            $this->redirect('index', ['flag' => 'update_success']);
        } else {
            $this->error($flag);
        }

    }

    public function delete()
    {
        $id = $this->request->post('id');
        UserModel::destroy($id);
        return self::ajaxMsg();
    }

}