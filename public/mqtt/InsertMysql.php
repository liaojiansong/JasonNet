<?php
/**
 * 处理订阅消息并插入redis的脚本
 * User: Jason
 * Date: 2018/4/21
 * Time: 13:07
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class InsertMysql extends Base
{
    // TODO 思路分析
    // TODO 1 获取到当前redis内所有的设备数据,即data_list
    // TODO 2 设备数据会逐条逐条插入数据库,在这这钱要验证是否触发了触发器
    // TODO 3 具体流程如下
    // TODO 3.1 解析消息体,拿到发过来的值和设备id
    // TODO 3.2 根据设备id去找有没有设置触发器
    // TODO 3.3 如果设备设置了触发器(可能是多个)
    // TODO 3.4 进行遍历比对,如果触发了就将触发的值和当前的时间压进这个触发器里面,然后再将这个触发器的名称放进report_list里面
    // TODO 3.5 report报告器会定时遍历这个report_list,进项相应的报警操作
    /**
     * 获取设备数据
     * @return array
     */
    public function getDeviceDataBox()
    {
        $device_data_box = [];
        //  设备数据都存放在以'data_list_+ 设备id'的list中
        $list_keys = $this->redis->keys('data_list*');
        foreach ($list_keys as $key) {
            $one_data = [];
            // 获取list长度
            $len = $this->redis->lLen($key);
            // 获取list的过期时间
            $live_time = $this->redis->ttl($key);
            // 长度大于一才取数据
            if ($len > 1) {
                // 过期时间低于5秒要取走
                if ($live_time < 5) {
                    //TODO 若是该键无数据,会被自动清除,所以要留一个
                    for ($i = 1; $i < $len; $i++) {
                        // 弹出这个list的值
                        array_push($one_data, $this->redis->rPop($key));
                    }
                } else {
                    // 长度低于50 就取$len长度的 ,大于就取50
                    if ($len > 50) {
                        $len = 50;
                    }
                    for ($i = 1; $i < $len; $i++) {
                        array_push($one_data, $this->redis->rPop($key));
                    }
                }
                $device_data_box[$key] = $one_data;
            }
        }
        return $device_data_box;
    }

    /*
     * 插入表数据
     */
    public function insetIntoTable($device_data_box)
    {
        // TODO 报警器有问题
        foreach ($device_data_box as $val) {
            foreach ($val as $msg) {
                $msg = json_decode($msg);
                $payload = json_decode($msg->payload ?? null);
                if ($payload !== null) {
                    $device_id = $payload->device_id ?? null;
                    $data_content = $payload->data_content ?? null;
                    // 对比发送过过来的数据与触发器阈值
                    $this->checkALL($device_id, $data_content);
                    $insertData = [
                        'topic' => $msg->topic ?? null,
                        'device_id' => $device_id ,
                        'data_type' => $payload->data_type ?? null,
                        'data_content' => $payload->data_content,
                        'create_time' => $payload->create_time ?? null,
                        'update_time' => $payload->update_time ?? null,
                    ];
                    $this->mysql->insert(self::device_data_table, $insertData);
                }

            }
        }
    }

    /**
     * 循环插入数据
     */
    public function insertData()
    {
        while (true) {
            echo '开始执行插入数据库操作'."\n";
            $data_box = $this->getDeviceDataBox();
            $this->insetIntoTable($data_box);
            unset($data_box);
            sleep(18);
        }
    }

    /**
     * 检测是否触发规则,触发则写入redis
     * @param $device_id
     * 设备id
     * @param $need_check
     * 检测的值
     * @return bool
     */
    public function checkTouchOff($target_name,$need_check)
    {
        // 判断这个触发器是否存在
        $flag= $this->redis->exists($target_name);
        $is_report = false;
        if ($flag) {
            // 获取信息, 返回的是数组
            $target_info = $this->redis->hGetAll($target_name);
            // 触发条件
            $target_condition = $target_info['target_condition'];
            // 阈值
            $target_value = $target_info['target_value'];
            // 数字才判断
            if (is_numeric($need_check) && is_numeric($target_value)) {
                switch ($target_condition) {
                    case '>' :
                        if ($need_check > $target_value) {
                            $is_report = true;
                        }
                        break;
                    case '>=' :
                        if ($need_check >= $target_value) {
                            $is_report = true;
                        }
                        break;
                    case '<' :
                        if ($need_check < $target_value) {
                            $is_report = true;
                        }
                        break;
                    case '<=' :
                        if ($need_check <= $target_value) {
                            $is_report = true;
                        }
                        break;
                    case '==' :
                        if ($need_check == $target_value) {
                            $is_report = true;
                        }
                        break;
                    case 'change' :
                        if ($need_check != $target_value) {
                            $is_report = true;
                        }
                        break;
                }
            }
        }

        if ($is_report) {
//            echo '-----------------------被触发的触发器-------------------------------------';
//            echo "\n";
//            var_dump($target_name);
//            echo "\n";
            // 将发送过来比较的值追加到$target_name
            $this->redis->hMset($target_name, [
                'target_time'=> date('Y-m-d H:i:s'),
                'send_value'=> $need_check,
            ]);
            // report_list装的是要进行报告的触发器集合
            $this->redis->lPush(self::REPORT_LIST, $target_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检测是否超过阈值
     * @param $device_id
     * 设备id
     * @param $need_check
     * 需要校验的值
     */
    public function checkALL($device_id, $need_check)
    {
        // 拼装触发器前缀 如:target_+设备id
        $target_names = self::TARGET . $device_id.'_';

        // 获取这个设备的所有触发器
        $list_keys = $this->redis->keys($target_names.'*');
        if (!empty($list_keys)) {
            // 遍历检验
            foreach ($list_keys as $name) {
                $this->checkTouchOff($name, $need_check);
            }
        }
    }


}



$obj = new InsertMysql();
$obj->insertData();