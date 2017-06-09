<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");
// 单品查看
if(isset($_GET['look_one'])){
	$goods_code = $_GET['look_one'];
	$s_date = $_GET['s_date'];
	$e_date = $_GET['e_date'];

	$sql = "SELECT goods_code,sum(out_num) AS sum_out_num,sum(pause_ch) AS sum_pause_ch,sum(pause_jp) AS sum_pause_jp FROM history_send WHERE goods_code = '{$goods_code}' AND import_day BETWEEN '{$s_date}' AND '{$e_date}'";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 下载出库表
if(isset($_GET['out_table'])){
	$out_table = $_GET['out_table'];
	$s_date = $_GET['s_date'];
	$e_date = $_GET['e_date'];

    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel 

    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:Z')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->getColor()->setRGB('36a38b');//前景色
    // $objSheet->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // $objSheet->getStyle('A1:B1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    // $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    // $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

	if($out_table == 'out_detail_table'){
		$t_title = '明细@';
		$objSheet->setCellValue("A1","出库日期")
			->setCellValue("B1","商品代码")
            ->setCellValue("C1","出仓")
            ->setCellValue("D1","出货数")
            ->setCellValue("E1","扣中国")
            ->setCellValue("F1","扣日本")
            ->setCellValue("G1","物流")
            ->setCellValue("H1","店铺")
            ->setCellValue("I1","订单号")
            ->setCellValue("J1","收件人")
            ->setCellValue("K1",$s_date)
            ->setCellValue("L1",$e_date);

		$sql_line = "SELECT * FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}'";
		$res = $db->getAll($sql_line);

        $j=2;
	    foreach ($res as $key => $value) {
	        $objSheet->setCellValue("A".$j,$value['import_day'])
	                ->setCellValueExplicit("B".$j,$value['goods_code'],PHPExcel_Cell_DataType::TYPE_STRING)
	                ->setCellValue("C".$j,$value['repo_status'])
	                ->setCellValue("D".$j,$value['out_num'])
	                ->setCellValue("E".$j,$value['pause_ch'])
	                ->setCellValue("F".$j,$value['pause_jp'])
	                ->setCellValue("G".$j,'<'.$value['express_company'].'>'.$value['send_method'].$value['oms_order_express_num'])
	                ->setCellValue("H".$j,$value['store_name'])
	                ->setCellValue("I".$j,$value['order_id'])
	                ->setCellValue("J".$j,$value['who_name']);
	        $j++;
	    }
            
	}else if($out_table == 'out_goods_table'){
		$t_title = '物料结算@';
		$objSheet
			->setCellValue("A1","商品代码")
            ->setCellValue("B1","出货数")
            ->setCellValue("C1","扣中国")
            ->setCellValue("D1","扣日本")
            ->setCellValue("E1",$s_date)
            ->setCellValue("F1",$e_date);

		$sql_line = "SELECT goods_code,sum(out_num) AS sum_out_num,sum(pause_ch) AS sum_pause_ch,sum(pause_jp) AS sum_pause_jp FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}' GROUP BY goods_code ORDER BY sum_out_num DESC";
		$res = $db->getAll($sql_line);

		$j=2;
	    foreach ($res as $key => $value) {
	        $objSheet
	                ->setCellValueExplicit("A".$j,$value['goods_code'],PHPExcel_Cell_DataType::TYPE_STRING)
	                ->setCellValue("B".$j,$value['sum_out_num'])
	                ->setCellValue("C".$j,$value['sum_pause_ch'])
	        		->setCellValue("D".$j,$value['sum_pause_jp']);
	        $j++;
	    }

	}else if($out_table == 'out_daybyday_table'){
		$t_title = '物料日结算@';
		$objSheet
			->setCellValue("A1","商品代码")
            ->setCellValue("B1","出货数")
            ->setCellValue("C1","扣中国")
            ->setCellValue("D1","扣日本")
            ->setCellValue("E1","出货日期")
            ->setCellValue("F1",$s_date)
            ->setCellValue("G1",$e_date);

		$sql_line = "SELECT goods_code,sum(out_num) AS sum_out_num,sum(pause_ch) AS sum_pause_ch,sum(pause_jp) AS sum_pause_jp,import_day FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}' GROUP BY import_day,goods_code ORDER BY import_day DESC";
		$res = $db->getAll($sql_line);
		
		$j=2;
	    foreach ($res as $key => $value) {
	        $objSheet
	                ->setCellValueExplicit("A".$j,$value['goods_code'],PHPExcel_Cell_DataType::TYPE_STRING)
	                ->setCellValue("B".$j,$value['sum_out_num'])
	                ->setCellValue("C".$j,$value['sum_pause_ch'])
	        		->setCellValue("D".$j,$value['sum_pause_jp'])
	        		->setCellValue("E".$j,$value['import_day']);
	        $j++;
	    }
	}

	$objSheet->setTitle($t_title.$now_time);//表名
    
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/".$out_table.".xlsx");   //保存在服务器
    echo "ok";
}