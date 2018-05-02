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
        dump($target_info);
    }

    /**
     * 报告发送邮件的结果(写进数据表)
     */
    public function responseEmailResult()
    {

    }

    public function getReportInfo()
    {
        // todo 每一个target_device 包含完成的报警信息
        $report_key = 'report_list';
        $len = $this->redis->lLen($report_key);
        if ($len > 1) {
            for ($i = 0; $i < $len; $i++) {
                $one_target_device = $this->redis->rPop($report_key) ?? null;
                if ($one_target_device != null) {
                    if ($this->redis->exists($one_target_device))
                    $target_info = $this->redis->hGetAll($one_target_device);
                    $this->sendEmail($target_info);
                }
            }
        }
    }
}

$obj = new Report();
