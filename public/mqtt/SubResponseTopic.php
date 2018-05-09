<?php
/**
 *
 * @User: Jason
 * @Date: 2018/5/9
 */
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
require_once('Base.php');
class SubResponseTopic extends Base
{
    public function __construct()
    {
        parent::__construct(false, true, true);

    }



    /**
     * 订阅并写入redis
     */
    public function sub()
    {
        $this->mqtt->subscribe('response_1', 1);
        $this->mqtt->onMessage(function ($msg) {
            $payload = json_decode($msg->payload);
            echo '-----------------------响应信息-------------------------------------';
            echo "\n";
            var_dump($payload);
            echo "\n";
        });
        $this->mqtt->loopForever();
    }
}

$user = new SubResponseTopic();
$user->sub();