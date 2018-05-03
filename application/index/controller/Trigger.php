<?php
/**
 * 项目: net
 * 作者: Jason
 * 日期: 2018/4/24
 * 时间: 21:21
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\DeviceLogModel;
use app\index\model\TriggerModel;
use think\Db;
use think\Session;
use function request;

class Trigger extends BaseController
{
    public function index()
    {
        $list = TriggerModel::with('device')->where('product_id',Session::get('product_id'))->order('id','desc')->paginate(5);
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
            Db::startTrans();
            try {
                $trigger_id = TriggerModel::CreateWithID($param);
                if ($trigger_id) {
                    $param['trigger_id'] = $trigger_id;
                    TriggerModel::addTargetIntoRedis($param['device_id'], $param);
                    DeviceLogModel::Log($param['device_id'], 'add_trigger', '添加触发器：' . $param['trigger_name'] ?? null);
                    Db::commit();
                    $this->redirect('index', ['flag' => 'create_success']);
                } else {
                    $this->error('添加触发器失败');
                }
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
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
            Db::transaction(function () use ($param) {
                TriggerModel::addTargetIntoRedis($param['device_id'], $param);
                DeviceLogModel::Log($param['device_id'], 'update_trigger', '更新触发器：' . $param['trigger_name'] ?? null);
                $device = new TriggerModel();
                $device->newUpdate($param['id'], $param);
            });
            $this->redirect('index', ['flag' => 'update_success']);
        } else {
            $this->error($flag);
        }

    }
    public function delete()
    {
        $id = $this->request->post('id');
        $param = TriggerModel::get($id);
        DeviceLogModel::Log($param['device_id'], 'del_trigger', '删除触发器：' . $param['trigger_name'] ?? null);
        $param->delete();
        return self::ajaxMsg();
    }


}