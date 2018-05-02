<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 16:16
 */

namespace app\common\validate;


use think\Validate;

class CommonValidate extends Validate
{
    protected $rule = [
        'username' => 'require|min:3|max:25',
        'phone' => 'number|length:11',
        'email' => 'email',
        'password' => 'require|min:6|max:32|alphaNum',
        'confirm_password' => 'require|confirm:password',


        /**
         * 设备添加
         */
        'device_name' => 'require|max:25',
        'device_auth' => 'require',

        /**
         * 产品添加
         */
        'product_name' => 'require',
        'product_industry_id' => 'require',
        'product_description' => 'require',

        /**
         * 触发器添加
         */
        'device_id' => 'require',
        'trigger_name' => 'require',
        'target_condition' => 'require',
        'trigger_value' => 'require',

        /**
         * 数据流模板添加
         */
        'data_template_name' => 'require',
        'unit_name' => 'require',
        'unit_symbol' => 'require',

    ];
    protected $message = [
        'username.require' => '名称必须',
        'username.min' => '名称至少3个字符',
        'username.max' => '名称最多不能超过25个字符',
        'phone.number' => '请输入正确的电话号码',
        'phone.length' => '请输入11位的电话号码',
        'email' => '邮箱格式错误',
        'password.require' => '请输入密码',
        'password.min' => '密码至少6位',
        'password.max' => '密码至多32位',
        'password.alphaNum' => '密码只能是数字或字母',
        'confirm_password.require' => '请输入确认密码',
        'confirm_password.confirm' => '两次密码不一致',

        /**
         * 设备添加
         */
        'device_name.require' => '设备名称必须',
        'device_auth.require' => '设备鉴权信息必须',

        /**
         * 产品添加
         */
        'product_name.require' => '产品名必须',
        'product_industry_id.require' => '产品行业必须',
        'product_description.require' => '产品描述必须',

        /**
         * 触发器添加
         */
        'device_id.require' => '关联设备出错',
        'trigger_name.require' => '触发器名称必须',
        'target_condition.require' => '触发条件必须',
        'trigger_value.require' => '阈值必须',
        /**
         * 数据流模板添加
         */
        'data_template_name.require' => '模板名称必须',
        'unit_name.require' => '单位名称必须',
        'unit_symbol.require' => '单位符号必须',

    ];
    protected $scene = [
        'edit' => ['name', 'age'],
        'add_user' => ['name', 'phone', 'email', 'password', 'confirm_password'],
        'add_device' => ['device_name', 'device_auth'],
        'add_product' => ['product_name', 'product_industry','product_description'],
        'add_trigger' => ['device_id', 'trigger_name','target_condition','trigger_value'],
        'add_template' => ['data_template_name', 'unit_name','unit_symbol'],
    ];

}