<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/23
 * Time: 21:09
 */
require_once('Base.php');
class Test extends Base
{
    public function __construct()
    {
        parent::__construct(true, false, true);
    }

    public function subData($topic = 'data')
    {
        $this->mqtt->subscribe($topic, 1);
        $this->mqtt->onMessage(function ($msg) {
            echo '-----------------------数据信息-------------------------------------';
            echo "\n";
            var_dump($msg);
            echo "\n";
        });
//        $this->subAuth();
        $this->mqtt->loopForever();
    }

    public function subAuth($topic = 'auth')
    {
        $this->mqtt->subscribe($topic, 1);
        $this->mqtt->onMessage(function ($msg) {
            echo '-----------------------鉴权信息-------------------------------------';
            echo "\n";
            var_dump($msg);
            echo "\n";

        });
    }



}

$obj = new Test();
$obj->subData();


