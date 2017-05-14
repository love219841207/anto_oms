<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 读取所有冻结订单info表
if(isset($_GET['pause_order'])){
	$id = $_GET['pause_order'];
	//	获取所有平台 ********************
	$sql = "SELECT * FROM amazon_response_info WHERE is_pause = 'pause' ORDER BY ID DESC";
	$res = $db->getAll($sql);

	echo json_encode($res);
}

// 下载冻结订单表
if(isset($_GET['down_pause_orders_table'])){
    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('冻结订单表@'.$now_time);//表名
    $objSheet->setCellValue("A1","店铺")
    		->setCellValue("B1","订单号")
    		->setCellValue("C1","商品代码")
    		->setCellValue("D1","数量")
    		->setCellValue("E1","押中国")
    		->setCellValue("F1","押日本")
    		->setCellValue("G1","子订单价")
            ->setCellValue("H1","代引金额");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(18);//单元格宽
    $objSheet->getColumnDimension('B')->setWidth(34);//单元格宽
    $objSheet->getColumnDimension('C')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT * FROM amazon_response_info WHERE is_pause = 'pause' ORDER BY ID DESC";
	$res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['store'])
        		->setCellValue("B".$j,$value['order_id'])
        		->setCellValue("C".$j,$value['goods_code'])
                ->setCellValueExplicit("D".$j,$value['goods_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("E".$j,$value['pause_ch'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("F".$j,$value['pause_jp'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("G".$j,$value['item_price'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("H".$j,$value['cod_money'],PHPExcel_Cell_DataType::TYPE_STRING);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/pause_orders_table.xlsx");   //保存在服务器
    echo "ok";
}