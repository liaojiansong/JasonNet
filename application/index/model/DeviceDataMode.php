<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 22:42
 */

namespace app\index\model;


use app\common\BaseModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DeviceDataMode extends BaseModel
{
    protected $table = 'device_data';

    /**
     * 获取数据总数,昨日数据,近7日数据总数
     * @param $id
     * 设备id
     * @return array
     * 统计数组
     */
    public static function getCount($id)
    {
        // TODO 虚拟机时间不对
        // 总数
        $total = self::where('device_id', $id)->count();
        // 近7天
        $last_week = self::where('device_id', $id)->whereTime('create_time', 'week')->count() ;
        // 昨天
        $yesterday = self::where('device_id', $id)->whereTime('create_time', 'yesterday')->count();
        return [
            'total' => $total,
            'last_week' => $last_week,
            'yesterday' => $yesterday,
        ];
    }

    /**
     * 获取当前设备的数据总数
     * @param $device_id
     * 设备id
     * @return int|string
     */
    public static function getTotal($device_id)
    {
        $total  = self::where('device_id', $device_id)->count();
        return $total;
    }

    public static function excelBasic(array $data, $file_name, $title = '2018')
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('JasonNet.com')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('设备数据导出')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', '记录ID')
            ->setCellValue('B1', '订阅的主题')
            ->setCellValue('C1', '设备ID')
            ->setCellValue('D1', '数据类型')
            ->setCellValue('E1', '数据内容')
            ->setCellValue('F1', '创建时间')
            ->setCellValue('G1', '更新时间')
            ->fromArray($data, '无', 'A2');

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle($title);

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