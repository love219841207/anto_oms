<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");
// 单品查看
if(isset($_GET['look_sell'])){
	$s_date = $_GET['s_date'];
	$e_date = $_GET['e_date'];

	$sql = "SELECT sum(total_money) AS sum_total_money,sum(ems_money) AS sum_ems_money,sum(bill) AS sum_bill,sum(point) AS sum_point,sum(cheap) AS sum_cheap,sum(tax) AS sum_tax,sum(buy_money) AS sum_buy_money FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}'";
	$res = $db->getAll($sql);
	$final_res['table'] = $res; 

	$sql = "SELECT import_day,sum(total_money) AS sum_total_money,sum(ems_money) AS sum_ems_money,sum(bill) AS sum_bill,sum(point) AS sum_point,sum(cheap) AS sum_cheap,sum(tax) AS sum_tax,sum(buy_money) AS sum_buy_money FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}' GROUP BY import_day";
	$res = $db->getAll($sql);
	$final_res['chart'] = $res; 

	// 图表
	$labels = array();
	$sum_total_money = array();
	$sum_ems_money = array();
	$sum_bill = array();
	$sum_point = array();
	$sum_cheap = array();
	$sum_tax = array();
	$sum_buy_money = array();

	foreach ($res as $val) {
		array_push($labels, $val['import_day']);
		array_push($sum_total_money, $val['sum_total_money']);
		array_push($sum_ems_money, $val['sum_ems_money']);
		array_push($sum_bill, $val['sum_bill']);
		array_push($sum_point, $val['sum_point']);
		array_push($sum_cheap, $val['sum_cheap']);
		array_push($sum_tax, $val['sum_tax']);
		array_push($sum_buy_money, $val['sum_buy_money']);
	}

	$labels = implode(',', $labels);
	$sum_total_money = implode(',', $sum_total_money);
	$sum_ems_money = implode(',', $sum_ems_money);
	$sum_bill = implode(',', $sum_bill);
	$sum_point = implode(',', $sum_point);
	$sum_cheap = implode(',', $sum_cheap);
	$sum_tax = implode(',', $sum_tax);
	$sum_buy_money = implode(',', $sum_buy_money);

	$final_res['labels'] = $labels;
	$final_res['sum_total_money'] = $sum_total_money;
	$final_res['sum_ems_money'] = $sum_ems_money;
	$final_res['sum_bill'] = $sum_bill;
	$final_res['sum_point'] = $sum_point;
	$final_res['sum_cheap'] = $sum_cheap;
	$final_res['sum_tax'] = $sum_tax;
	$final_res['sum_buy_money'] = $sum_buy_money;

	echo json_encode($final_res);
}

// 下载出库表
if(isset($_GET['sell_detail_table'])){
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

	$t_title = '销售明细@';
	$objSheet->setCellValue("A1","出库日期")
        ->setCellValue("B1","店铺")
        ->setCellValue("C1","担当")
		->setCellValue("D1","店铺SKU")
		->setCellValue("E1","发送商品代码")
		->setCellValue("F1","数量")
		->setCellValue("G1","单价")
        ->setCellValue("H1","合計金額")
        ->setCellValue("I1","購入送料")
        ->setCellValue("J1","購入手数料")
        ->setCellValue("K1","購入ポイント")
        ->setCellValue("L1","購入クーポン")
        ->setCellValue("M1","購入消費税")
        ->setCellValue("N1","订单号")
        ->setCellValue("O1","收件人")
        ->setCellValue("P1","配送地址")
        ->setCellValue("Q1","配送邮编")
        ->setCellValue("R1","配送电话")
        ->setCellValue("S1","快递详情")
        ->setCellValue("T1","购买人邮箱")
        ->setCellValue("U1",$s_date)
        ->setCellValue("V1",$e_date);

	$sql_line = "SELECT * FROM history_send WHERE import_day BETWEEN '{$s_date}' AND '{$e_date}'";
	$res = $db->getAll($sql_line);

    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['import_day'])
                ->setCellValue("B".$j,$value['store_name'])
                ->setCellValue("C".$j,$value['holder'])
                ->setCellValue("D".$j,$value['sku'])
                ->setCellValueExplicit("E".$j,$value['goods_code'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("F".$j,$value['out_num'])
                ->setCellValue("G".$j,$value['unit_price'])
                ->setCellValueExplicit("H".$j,$value['total_money'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("I".$j,$value['ems_money'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("J".$j,$value['bill'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("K".$j,$value['point'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("L".$j,$value['cheap'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("M".$j,$value['tax'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("N".$j,$value['order_id'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("O".$j,$value['receive_name'])
                ->setCellValue("P".$j,$value['receive_house'])
                ->setCellValue("Q".$j,$value['receive_code'])
                ->setCellValue("R".$j,$value['receive_phone'])
                ->setCellValue("S".$j,'<'.$value['express_company'].'>'.$value['send_method'].$value['oms_order_express_num'])
                ->setCellValue("T".$j,$value['who_email']);
        $j++;
    }

	$objSheet->setTitle($t_title.$now_time);//表名
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/sell_detail_table.xlsx");   //保存在服务器
    echo "ok";
}