<?php
/**
 * 报警类
 * @User: Jason
 * @Date: 2018/4/26
 */
require_once('Base.php');

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

//require '../../vendor/autoload.php';
//
//require '../../vendor/phpmailer/phpmailer/src/Exception.php';
//require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
//require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
class Report extends Base
{
    /**
     * 发送邮件
     * @param $target_info
     */
    public function sendEmail($target_info)
    {
        $subject = "您的ID为{$target_info['device_id']}的设备的{$target_info['trigger_name']}被触发，详情请打开邮件";
        $body = "<h3>触发器信息</h3>
                <br/>
                <p>触发器ID：{$target_info['trigger_id']}</p>
                <p>触发器名称：{$target_info['trigger_name']}</p>
                <p>触发类型：{$target_info['target_condition']}</p>
                <p>触发条件：{$target_info['target_value']}</p>
                <br/>
                <br/>
                <h3>触发信息</h3>
                <br/>
                <p>设备ID：{$target_info['device_id']}</p>
                <p>触发的值：{$target_info['send_value']}</p>
                <p>触发时间：{$target_info['target_time']}</p>";
        // 写进报警日志
        $file = fopen('report.log', 'a+');
        $flag = fwrite($file, $subject . "\n\n\n" . $body . "\n\n\n");
        fclose($file);
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
        $len = $this->redis->lLen(self::REPORT_LIST);
        if ($len > 1) {
            for ($i = 0; $i < $len; $i++) {
                // 报警列表存的是需要报警的target_name
                $one_target_name = $this->redis->rPop(self::REPORT_LIST) ?? null;
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
            }
        }
    }

    public function loopReport()
    {
        while (true) {
            $this->getReportInfo();
            sleep(50);
        }

    }
}

$obj = new Report();
$obj->loopReport();

