<?php
/**
 * 响应命令（简单响应，不再推送）
 * @User: Jason
 * @Date: 2018/5/4
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class ResponseOrder extends Base
{
    public function __construct()
    {
        parent::__construct(true, true, true);
    }

    public function sub($topic = 'order')
    {
        $this->mqtt->subscribe($topic, 1);
        $this->mqtt->onMessage(function ($msg) {
            echo '-----------------------命令信息-------------------------------------';
            echo "\n";
            var_dump($msg);
            echo "\n";
            $payload = json_decode($msg->payload);
            if ($payload->data_type == 'order') {
                // TODO 设备做相应的判断
                $device_id = $payload->device_id;
                $this->Log($device_id, 'response_order',  '响应命令'.$payload->data_content ?? null.'  成功');
            }
        });
        $this->mqtt->loopForever();
    }

}

$obj = new ResponseOrder();
$obj->sub();