<?php

namespace app\index\controller;

use app\common\BaseController;
use app\index\model\DeviceDataMode;
use app\index\model\DevicesModel;
use think\Session;
use function request;

class Devices extends BaseController
{
    /**
     * 设备列表主页
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        request()->has('product_id') ? Session::set('product_id',request()->param('product_id')) : null;
        $devices_list = DevicesModel::where('product_id',Session::get('product_id'))->paginate(2);
        $this->assign([
            'devices_list' => $devices_list,
        ]);
        return $this->fetch('device-list');
    }

    /**
     * 添加设备(ajax)
     * @return array
     */
    public function create()
    {
        $flag = $this->validate($this->request->param(), 'CommonValidate.add_device');
        // 验证成功
        if ($flag === true) {
            $res = DevicesModel::newCreate($this->request->param());
            if ($res) {
                $is_success = true;
                $msg = "设备添加成功,ID为:" . $res;
            } else {
                $is_success = false;
                $msg = '添加失败';
            }
            // 验证失败
        } else {
            $is_success = false;
            $msg = $flag;
        }
        return self::ajaxMsg($is_success, $msg);
    }

    /**
     * 设备编辑页面
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $one = DevicesModel::get($id)->hidden(['create_time', 'update_time'])->toJson();
        $this->assign('one', $one);
        return $this->fetch('device-edit');
    }

    public function update()
    {
        $param = request()->param();
        $flag = $this->validate($param, 'CommonValidate.add_device');
        if ($flag === true) {
            $device = new DevicesModel();
            $device->newUpdate($param['id'], $param);
            return self::ajaxMsg(true, '编辑成功');
        } else {
            return self::ajaxMsg(false, $flag);
        }
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $one = DevicesModel::get($id)->hidden(['create_time', 'update_time']);
        $items = $one->deviceData()->limit(15)->select();
        $all_count = DeviceDataMode::getCount($id);

        $info = [];
        foreach ($items as $value) {
            array_push($info, $value->data_content);
        }

        $this->assign([
            'one' => $one,
            'item' =>$items,
            'info' =>json_encode($info),
            'all_count' => $all_count,
        ]);
        return $this->fetch('device-detail');
    }


    /**
     * 删除设备(ajax)
     * @return array
     */
    public function delete()
    {
        $id = $this->request->param('id');
        DevicesModel::destroy($id);
        return self::ajaxMsg();
    }

    /**
     * 发送命令
     * @return array
     */
    public static function sendOrder()
    {
        $order_info = request()->param();
        $msg = "向设备ID:{$order_info['device_id']}发送命令成功";
        return self::ajaxMsg(true, $msg);
    }

}
