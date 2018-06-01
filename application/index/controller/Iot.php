<?php
/**
 *
 * @User: Jason
 * @Date: 2018/5/25
 */

namespace app\index\controller;


use think\Controller;

class Iot extends Controller
{
    public function index()
    {
        return $this->fetch('index');
    }

}