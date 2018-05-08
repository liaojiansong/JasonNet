<?php
/**
 *
 * @User: Jason
 * @Date: 2018/5/3
 */

namespace app\index\model;


use app\common\BaseModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DeviceLogModel extends BaseModel
{
    protected $table = 'device_log';

    public static function Log($device_id, $record_type, $content)
    {
        /**
         * 日志类型,int=>登入,out=>注销,send_order=>发送命令,add_trigger=>添加触发器，del_trigger=>删除触发器
         */
        $data = [
            'device_id' => $device_id,
            'record_type' => $record_type,
            'content' => $content,
            'create_time' => time(),
            'update_time' => time(),
        ];
        self::create($data);
    }

    public function device()
    {
        return $this->belongsTo('DevicesModel', 'device_id');
    }

    /**
     * @param array $data
     * 数据(数组)
     * @param $file_name
     * 保存的文件名
     * @param string $time_range
     * 时间范围(用于sheet名称)
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function excelBasic(array $data, $file_name, $time_range = '2018')
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('JasonNet.com')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('设备日志数据导出')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', '记录ID')
            ->setCellValue('B1', '设备ID')
            ->setCellValue('C1', '日志类型')
            ->setCellValue('D1', '日志内容')
            ->setCellValue('E1', '创建时间')
            ->setCellValue('F1', '更新时间')
            ->fromArray($data, '无', 'A2');

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle($time_range);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

}