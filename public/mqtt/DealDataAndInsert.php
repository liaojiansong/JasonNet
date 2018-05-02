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
class DealDataAndInsert extends Base
{
    /**
     * 获取设备数据
     * @return array
     */
    public function getDeviceDataBox()
    {
        $device_data_box = [];
        $list_keys = $this->redis->keys('data_list*');
        foreach ($list_keys as $key) {
            $one_data = [];
            $len = $this->redis->lLen($key);
            $live_time = $this->redis->ttl($key);
            if ($len > 1) {
                if ($live_time < 5) {
                    //TODO 若是该键无数据,会被自动清除,所以要留一个
                    for ($i = 1; $i < $len; $i++) {
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
        foreach ($device_data_box as $val) {
            foreach ($val as $msg) {
                $msg = json_decode($msg);
                $payload = json_decode($msg->payload ?? null);
                var_dump($payload);
                if ($payload !== null) {
                    $devices_id = $payload->devices_id ?? null;
                    $data_content = $payload->data_content ?? null;
                    $this->checkTouchOff($devices_id, $data_content);
                    $insertData = [
                        'topic' => $msg->topic ?? null,
                        'devices_id' => $devices_id ,
                        'data_type' => $payload->data_type ?? null,
                        'data_content' => $payload->data_content,
                        'create_time' => $payload->create_time ?? null,
                        'update_time' => $payload->update_time ?? null,
                    ];
                    $this->mysql->insert(self::table, $insertData);
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
            $data_box = $this->getDeviceDataBox();
            $this->insetIntoTable($data_box);
            unset($data_box);
            sleep(58);
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
    public function checkTouchOff($device_id,$need_check)
    {
        $target_name = 'target_' . $device_id;
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
            // 将发送的值写进$target_name
            $this->redis->hSet($target_name, 'send_value', $need_check);
            $this->redis->lPush('report_list', $target_name);
            return true;
        } else {
            return false;
        }
    }

}

$obj = new DealDataAndInsert();
$obj->insertData();