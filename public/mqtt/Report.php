<?php
/**
 * 报警类
 * @User: Jason
 * @Date: 2018/4/26
 */
require_once('Base.php');

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

class Report extends Base
{
    const TOKEN = 'taukoqrjhgexbdhh';
    const MY_EMAIL = "729631422@qq.com";

    /**
     * 发送邮件
     * @param $target_info
     */
    public function sendEmail($target_info)
    {
        $subject = "您的ID为：{$target_info['device_id']}的设备的{$target_info['trigger_name']}被触发，详情请打开邮件";
        $body = "<h3>触发器信息</h3>
                <br/>
                <p>触发器ID：{$target_info['trigger_id']}</p>
                <p>触发器名称：{$target_info['trigger_name']}</p>
                <p>触发类型：{$target_info['target_condition']}</p>
                <p>触发条件：{$target_info['target_value']}</p>
                <br/>
                <br/>
                <h3>触发详情</h3>
                <br/>
                <p>设备ID：{$target_info['device_id']}</p>
                <p>触发的值：<span style='color: rgba(218,16,10,0.85)'>{$target_info['send_value']}</span></p>
                <p>触发时间：{$target_info['target_time']}</p>";
        // 写进报警日志
//        $file = fopen('report.log', 'a+');
//        $flag = fwrite($file, $subject . "\n\n\n" . $body . "\n\n\n");
//        fclose($file);
        $email = $target_info['email'] ?? null;
        if ($email != null) {
            $flag = self::send($email, $subject, $body);
        }else{
            $flag = false;
        }
        return $flag;
    }

    /**
     * 报告发送邮件的结果(写进数据表)
     *
     */
    public function insertIntoLog($target_info)
    {
        $this->Log($target_info['device_id'], 'report', '触发器：“' . $target_info['trigger_name'] . '”被触发');
    }

    public function getReportInfo()
    {
        // todo 每一个target_device 包含完成的报警信息
        $len = $this->redis->sCard(self::REPORT_LIST);
        if ($len > 0) {
            for ($i = 0; $i < $len; $i++) {
                // 报警列表存的是需要报警的target_name
                $one_target_name = $this->redis->sPop(self::REPORT_LIST) ?? null;

                if ($one_target_name != null) {
                    if ($this->redis->exists($one_target_name))
                        $target_info = $this->redis->hGetAll($one_target_name);
                    // TODO 返回标志位，是否报警成功 不成功再压回去
                    $flag = $this->sendEmail($target_info);
                    if ($flag) {
                        $this->insertIntoLog($target_info);
                    }
//                    if ($flag == false) {
//                        $this->redis->lPush(self::REPORT_LIST,$one_target_name);
//                    }
                }
                unset($one_target_name);
            }
        }
    }

    public function loopReport()
    {
        while (true) {
            $this->getReportInfo();
            sleep(25);
        }

    }

    /**
     * 发送邮件
     * @param $address
     * 收件人地址
     * @param $nickname
     * 收件人昵称
     * @param $subject
     * 邮件标题
     * @param $body
     * 邮件内容
     * @return bool
     * 成功标志
     * @throws Exception
     */
    public static function send($address, $subject, $body,$nickname = '尊敬的用户')
    {
        $mail = new PHPMailer(); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;  //启用SMTP认证
        // 链接qq域名邮箱的服务器地址
        $mail->Host = 'smtp.qq.com';
        // 设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        // 设置ssl连接smtp服务器的远程服务器端口号
        $mail->Port = 465;


        $mail->CharSet = "UTF-8"; //字符集
        $mail->Encoding = "base64"; //编码方式


        $mail->Username = self::MY_EMAIL;  //你的邮箱 163的
        $mail->Password = self::TOKEN;  //你的密码
        $mail->Subject = $subject; //邮件标题 任意

        $mail->From = self::MY_EMAIL;  //发件人地址（也就是你的邮箱）
        $mail->FromName = "JasonNet";  //发件人姓名 任意


        $mail->AddAddress($address, $nickname);//添加收件人（地址，昵称）

        $mail->IsHTML(true); //支持html格式内容
        $mail->Body = $body; //邮件主体内容

        //发送
        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }

}

$obj = new Report();
$obj->loopReport();
//Report::send('liao_jian_song@163.com', '手机高温报警', '小松果，你的小米8手机温度过高');

