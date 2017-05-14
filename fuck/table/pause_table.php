<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 读取冻结表
if(isset($_GET['look_pause'])){
	$sql = "SELECT goods_code,sum(goods_num)-sum(pause_ch)-sum(pause_jp) AS pause_num FROM amazon_response_info WHERE is_pause = 'pause' group by goods_code";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 下载冻结表
if(isset($_GET['down_pause'])){
    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('统计冻结表@'.$now_time);//表名
    $objSheet->setCellValue("A1","商品代码")
            ->setCellValue("B1","冻结数");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:B')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:B1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT goods_code,sum(goods_num)-sum(pause_ch)-sum(pause_jp) AS pause_num FROM amazon_response_info WHERE is_pause = 'pause' group by goods_code";
    $res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['goods_code'])
                ->setCellValueExplicit("B".$j,$value['pause_num'],PHPExcel_Cell_DataType::TYPE_STRING);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/pause_table.xlsx");   //保存在服务器
    echo "ok";
}