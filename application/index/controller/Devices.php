<?php

namespace app\index\controller;

use app\common\BaseController;
use app\common\BaseModel;
use app\index\model\DataTemplateModel;
use app\index\model\DeviceDataMode;
use app\index\model\DeviceLogModel;
use app\index\model\DevicesModel;
use think\Session;
use function explode;
use function request;
use function strtotime;
use function trim;

class Devices extends BaseController
{
    /**
     * 设备列表主页
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 数据流模板
        $template_options = DataTemplateModel::getTemplateOptions();
        $devices_list = DevicesModel::where('product_id', Session::get('product_id'))->paginate(5);
        $this->assign([
            'devices_list' => $devices_list,
            'template_options' => $template_options,
        ]);
        return $this->fetch('device-list');
    }

    /**
     * 添加设备(ajax)
     * @return array
     */
    public function create()
    {
        $param = $this->request->param();
        $flag = $this->validate($param, 'CommonValidate.add_device');
        // 验证成功
        if ($flag === true) {
            $param['product_id'] = Session::get('product_id');
            $res = DevicesModel::newCreate($param);
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
        // 数据流模板
        $template_options = DataTemplateModel::getTemplateOptions();
        $id = $this->request->param('id');
        $one = DevicesModel::get($id)->hidden(['create_time', 'update_time'])->toJson();
        $this->assign([
            'one' => $one,
            'template_options' => $template_options,
        ]);
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
        $logs = DeviceLogModel::where('device_id', $id)->with('device')->order('create_time', 'DESC')->paginate(15);
        $template = $one->template;
        $items = $one->deviceData()->limit(25)->order('create_time')->select();
        $all_count = DeviceDataMode::getCount($id);
        // highchart 起始时间点
        $point_start = $items[0]['create_time'] ?? null;

        $info = [];
        foreach ($items as $value) {
//            $temp = [intval(strtotime($value->create_time)), floatval($value->data_content)];
            $info[] = floatval($value->data_content);
        }
        $this->assign([
            'one' => $one,
            'item' => $items,
            'info' => json_encode($info),
            'all_count' => $all_count,
            'logs' => $logs,
            'template' => $template,
            'point_start' => strtotime($point_start) * 1000,
        ]);
        return $this->fetch('device-detail');
    }


    public static function export()
    {
        $param =  request()->param();
        if (empty($param['device_id']) || empty($param['time_range'])) {
            return '数据有误,无法导出';
        }else{
            $time_range = explode('~', trim($param['time_range']));
            $start = strtotime($time_range[0]);
            $end = strtotime($time_range[1]);
            $data = DeviceDataMode::order('create_time', 'DESC')
                ->where('device_id', $param['device_id'])
                ->whereTime('create_time', 'between', [$start, $end])
                ->select()
                ->toArray();
            $filename = ($param['device_name'] ?? null) . '数据报表' . "({$param['time_range']})";
            DeviceDataMode::excelBasic($data, $filename,'数据详情');
        }
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
        $mqtt = BaseModel::getMqtt();
        $mqtt->loop();
        $payload = [
            'device_id' => $order_info['device_id'] ?? null,
            'data_type' => 'order',
            'data_content' => $order_info['order_text'] ?? null,
            'create_time' => time(),
            'update_time' => time(),
        ];
        // 发送向指定主题命令
        $mid = $mqtt->publish('order', json_encode($payload), 1, 0);
        $mqtt->loop();

        if ($mid) {
            $msg = "向设备ID:{$order_info['device_id']}发送命令成功";
            DeviceLogModel::Log($order_info['device_id'] ?? null, 'send_order', '发送命令：' . $order_info['order_text']);
            return self::ajaxMsg(true, $msg);
        } else {

            return self::ajaxMsg(false, '发送失败，请重试');
        }
        $mqtt->disconnect();
        unset($mqtt);
    }


}
