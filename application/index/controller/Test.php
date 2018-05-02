<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20
 * Time: 22:26
 */

namespace app\index\controller;


use app\index\model\UserModel;
use think\Controller;
use think\exception\DbException;

class Test extends Controller
{
    public function echoInfo()
    {
        try {
            dump(UserModel::all());
        } catch (DbException $e) {

        }
    }
}

$test = new Test();
$test->echoInfo();
