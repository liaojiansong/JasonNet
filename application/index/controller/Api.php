<?php
/**
 *
 * @User: Jason
 * @Date: 2018/5/5
 */

namespace app\index\controller;


use app\common\BaseController;
use app\index\model\DeviceDataMode;
use app\index\model\DeviceLogModel;
use app\index\model\DevicesModel;
use app\index\model\ProductModel;
use function count;
use function is_array;
use function is_numeric;
use function json_encode;
use function print_r;
use function request;


class Api extends BaseController
{
    const CODE = [
        'api_key' => 40001,// 缺少product_id参数
        'token' => 40002,// 缺少token参数
        'product_id' => 40003,// 缺少token参数
        'device_id' => 40004,// 缺少token参数
        'invalid' => 40005,// 验证失败，产品id与token不匹配
        'invalid_argument' => 40006,// 验证失败，产品id与token不匹配
        'type' => 40007,// 验证失败，缺乏请求类型
        'no_product' => 40008,// 产品不存在
        'no_device' => 40009,// 设备不存在
        'no_type' => 40010,// 请求类型出错
        'device_ids' => 40011,// 缺少设备id数组参数


    ];
    private static $redis = null;
    protected $beforeActionList = [
        'checkAuthFirst' => ['except' => 'index'],
    ];

    public function index()
    {


        return $this->fetch('api-list');
    }


    public static function product()
    {
        $param = request()->param();
        switch ($param['type']) {
            case 'get_device_list':
                self::getDeviceList($param);
                break;
            case 'get_statistical_info':
                self::getStatisticalInfo($param);
                break;
        }

    }

    /**
     * 获取设备列表
     * @param $param
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDeviceList($param)
    {
        $product = ProductModel::get($param['product_id']);
        if (empty($product)) {
            self::buildErrorMsg('no_product', 'no_product');
        }
        $product = $product->toArray();
        $devices = DevicesModel::where('product_id', $param['product_id'])->select()->toArray();
        $info = [
            'code' => 10000,
            'error' => 'success',
            'data' => [
                'product_id' => $product['id'],
                'product_name' => $product['product_name'],
                'device_count' => count($devices),
                'devices_list' => $devices
            ]
        ];
        echo $oo = json_encode($info);
    }

    /**
     * 获取产品统计详情
     * @param $param
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStatisticalInfo($param)
    {
        $product = ProductModel::get($param['product_id']);
        if (empty($product)) {
            self::buildErrorMsg('no_product', 'no_product');
        }
        $data_count = 0;
        $device_list = $product->devices()->select()->each(function ($item) use (&$data_count) {
            $data_count += DeviceDataMode::getTotal($item->id);
            return $item;
        });

        $info = [
            'code' => 10000,
            'error' => 'success',
            'data' => [
                'product_id' => $product['id'],
                'product_name' => $product['product_name'],
                'devices_count' => $device_list->count() ?? null,
                'triggers_count' => $product->triggers()->count() ?? null,
                'data_count' => $data_count ?? null
            ]
        ];
        echo $oo = json_encode($info);
    }

    /**
     * 设备
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function device()
    {
        $param = request()->param();
        switch ($param['type']) {
            case 'single_device_info':
                self::getSingleDeviceInfo($param);
                break;
            case 'get_statistical_info':
                self::getStatisticalInfo($param);
                break;
            case 'get_devices_status':
                self::getDevicesStatus($param);
                break;
            case 'get_devices_logs':
                self::getDevicesLogs($param);
                break;
            default:
                self::buildErrorMsg('no_type','no_type');
                break;
        }

    }

    /**
     * 获取单个设备的详情
     * @param $param
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSingleDeviceInfo($param)
    {
        $single_device = self::device_exist($param);
        $is_get_data = $param['is_get_data'] ?? null;
        // TODO 部署后记得开启
//        $single_device ['online'] = self::checkOnline($param['device_id']);
        if ($is_get_data) {
            $device_data = DeviceDataMode::where('device_id', $param['device_id'])
                ->order('create_time','desc')
                ->limit($param['offset'] ?? 0, $length = $param['length'] ?? 100)
                ->select()
                ->toArray();

            $info = [
                'code' => 10000,
                'error' => 'success',
                'data' => [
                    'device_info' => $single_device,
                    'device_data' => $device_data,
                ]
            ];

            echo $oo = json_encode($info);
        }else{
            $info = [
                'code' => 10000,
                'error' => 'success',
                'data' => [
                    'device_info' => $single_device
                ]
            ];
            print_r($info);

            echo $oo = json_encode($info);
        }
    }

    public static function getDevicesStatus($param)
    {
        self::checkParam(['device_ids',]);
        // 判断是否是数字
        if (!is_array($param['device_ids'])) {
            self::buildErrorMsg('invalid_argument');
        }
        $list = DevicesModel::whereIn('id', $param['device_ids'])
            ->select()
            ->each(function ($item) {
                // todo 部署后开启
//           $item->online = self::checkOnline($device_id);
                $item->online = 1;
                return $item;
            })->toArray();
        $info = [
            'code' => 10000,
            'error' => 'success',
            'data' => $list
        ];

        echo $oo = json_encode($info);

    }

    public static function getDevicesLogs($param)
    {
        $device_info = self::device_exist($param);
        $device_logs = DeviceLogModel::where('device_id', $param['device_id'])
            ->order('create_time', 'desc')
            ->limit($param['offset'] ?? 0, $length = $param['length'] ?? 100)
            ->select()
            ->toArray();
        $info = [
            'code' => 10000,
            'error' => 'success',
            'data' => [
                'device_info' => $device_info,
                'device_logs' => $device_logs,
            ]
        ];
        print_r($info);

        echo $oo = json_encode($info);

    }

    /**
     * 检查设备是否存在
     * @param $param
     * @return array
     * 返回设备信息
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function device_exist($param)
    {
        self::checkParam(['device_id',]);
        // 判断是否是数字
        if (!is_numeric($param['device_id'])) {
            self::buildErrorMsg('invalid_argument');
        }
        $single_device = DevicesModel::get($param['device_id'])->toArray();
        if (empty($single_device)) {
            self::buildErrorMsg('no_device');
        }
        return $single_device;
    }
    public static function trigger()
    {

    }

    public static function order()
    {

    }


    public static function checkAuthFirst()
    {
        self::checkParam(['product_id', 'api_key', 'type']);
        $param = request()->param();
        $flag = ProductModel::where('id', $param['product_id'])->where('api_key', $param['api_key'])->select()->isEmpty();
        if ($flag) {
            return self::buildErrorMsg('invalid');
        } else {
            return true;
        }
    }

    /**
     * 批量检查变量是否存在
     * @param array $param_names
     * @return bool
     */
    public static function checkParam(array $param_names)
    {
        if (!empty($param_names)) {
            foreach ($param_names as $value) {
                self::checkVar($value);
            }
        }
        return true;
    }

    /**
     * 检查变量是否存在
     * @param $var_name
     * @return bool
     */
    public static function checkVar($var_name)
    {
        $param = request()->param();
        if (isset($param[$var_name]) && $param[$var_name] != '') {
            return true;
        } else {
            self::buildErrorMsg($var_name);
        }
    }


    public static function buildErrorMsg($var_name, $msg = null)
    {
        if ($msg == null) {
            $msg = $var_name;
        }
        $info = [
            // 请求数据错误
            'code' => self::CODE[$var_name],
            'error' => $msg,
        ];
        echo json_encode($info);
        exit();
    }

    public static function checkOnline ($device_id)
    {
        $white_list_name = 'device_white_list_' . $device_id;
        if (self::$redis->exists($white_list_name)) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function initRedis()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        self::$redis = $redis;
    }

    public static function getRedis()
    {
        if (self::$redis instanceof \Redis) {
            return self::$redis;
        } else {
            self::initRedis();
        }
    }


}