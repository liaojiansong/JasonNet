<?php
/**
 * 脚本测试类
 * User: Jason
 * Date: 2018/5/31
 * Time: 21:09
 */
require_once('Base.php');

class Test extends Base
{
    private $device_id = 96549;
    public function __construct()
    {
        parent::__construct(false, true, true);
    }

    public function getInput()
    {
        $str = "请选择操作（输入数字）：
                1 ：发送鉴权信息\n
                2 ：发送数据信息\n";

        while (true) {
            $this->mqtt->loop();
            fwrite(STDOUT, $str);
            $input = trim(fgets(STDIN));
            if (!is_numeric($input)) {
                echo "非法输入\n";
                echo "\n";
                continue;
            }
            // 发送鉴权信息
            if ($input == 1) {
                $this->mqtt->loop();
                $payload = [
                    'api_key' => 'ODQ2MC42NDA1MDEwNDE1ODMxMjg=',
                    'product_id' => 20189598,
                    'device_id' => 96549,
                    'data_type' => 'auth',
                    'device_auth' => 'CO2_D_01',
                    'response_topic' => 'response_1',
                ];
                $mid = $this->mqtt->publish('auth', json_encode($payload), 1, 0);
                $this->mqtt->loop();
                echo "\nID为：96549 的设备正在发送鉴权信息，请稍后！\n\n";
                sleep(1);
                echo "正在校验鉴权是否成功\n\n";
                sleep(1);
                if ($this->existsWhiteList($this->device_id)) {
                    echo "鉴权成功，正在返回主菜单\n\n";
                } else {
                    echo "鉴权失败，请返回主菜单继续进行鉴权\n\n";
                }
            } elseif ($input == 2) {

                fwrite(STDOUT, "\n请输入设备ID：\n\n");
                $input_5 = trim(fgets(STDIN));
                if (!is_numeric($input_5)) {
                    echo "非法输入\n\n";
                    continue;
                }
                echo "\n您输入的设备ID为：{$input_5}\n\n";
                $this->device_id = $input_5;

                $input_6 = $this->sendData();

                if ($this->publishMsg($this->device_id, $input_6)) {
                    echo "\n正在发送中。。。\n";
                    echo "\n发送成功\n";
                    echo "\n";
                    $number = $this->continueSend();
                    if ($number == 1) {
                        $input_7 = $this->sendData();
                        $this->publishMsg($this->device_id, $input_7);
                        continue;
                    } else{
                        continue;
                    }

                }else{
                    echo "\n发送失败\n\n";
                }

            }
            $this->mqtt->loop();
        }
    }

    /**
     * 向主题发送数据
     * @param $device_id
     * @param $data
     * @return int
     */
    public function publishMsg($device_id, $data)
    {
        $this->mqtt->loop();
        $payload = [
            'device_id' => $device_id,
            'data_type' => 'data',
            'data_content' => $data,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $mid = $this->mqtt->publish('data', json_encode($payload), 1, 0);
        $this->mqtt->loop();
        return $mid;
    }

    /**
     * 询问要发送的数据
     * @return string
     */
    public function sendData()
    {
        fwrite(STDOUT, "\n请输入要发送的信息:");
        echo "\n";
        $input_6 = trim(fgets(STDIN));
        echo "\n您输入的信息为：{$input_6}\n";
        return $input_6;
    }

    /**
     * 询问是否继续操作
     * @return string
     */
    public function continueSend()
    {
        fwrite(STDOUT, "请选择一个操作：
        1 ：继续发送\n
        2 : 回到主菜单\n");
        $input_6 = trim(fgets(STDIN));
        return $input_6;
    }

}

$obj = new Test();
$obj->getInput();


