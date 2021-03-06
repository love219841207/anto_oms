<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");
// 查看销售额
if(isset($_GET['look_sell'])){
	$s_date = $_GET['s_date'];
	$e_date = $_GET['e_date'];

	$sql = "SELECT sum(total_money) AS sum_total_money,sum(ems_money) AS sum_ems_money,sum(bill) AS sum_bill,sum(point) AS sum_point,sum(cheap) AS sum_cheap,sum(tax) AS sum_tax,sum(buy_money)-sum(ems_money)-sum(bill)-sum(point)-sum(cheap)-sum(tax) AS sum_buy_money FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}'";
	$res = $db->getAll($sql);
	$final_res['table'] = $res;

	$sql = "SELECT express_day,sum(total_money) AS sum_total_money,sum(ems_money) AS sum_ems_money,sum(bill) AS sum_bill,sum(point) AS sum_point,sum(cheap) AS sum_cheap,sum(tax) AS sum_tax,sum(buy_money)-sum(ems_money)-sum(bill)-sum(point)-sum(cheap)-sum(tax) AS sum_buy_money FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' GROUP BY express_day";
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
		array_push($labels, $val['express_day']);
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

// 下载销售明细
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
    $objPHPExcel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

    $t_title = '销售明细@';
    $objSheet->setCellValue("A1","购买者")
        ->setCellValue("B1","配送者")
        ->setCellValue("C1","商品")
        ->setCellValue("D1","数量")
        ->setCellValue("E1","单价")
        ->setCellValue("F1","送料")
        ->setCellValue("G1","手数料")
        ->setCellValue("H1","消費税")
        ->setCellValue("I1","ポイント")
        ->setCellValue("J1","クーポン")
        ->setCellValue("K1","总金额")
        ->setCellValue("L1","发送日")
        ->setCellValue("M1","种类")
        ->setCellValue("N1","担当")
        ->setCellValue("O1","店铺")
        ->setCellValue("P1","追跡番号")
        ->setCellValue("Q1","包裹ID")
        ->setCellValue("R1","注文番号")
        // ->setCellValue("R1","配送电话")
        // ->setCellValue("T1","购买人邮箱")
        ->setCellValue("S1",$s_date)
        ->setCellValue("U1",$e_date);

    $sql_line = "SELECT * FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' ORDER BY send_id,id";
    $res = $db->getAll($sql_line);

    $j=2;
    $o_who_name = '';
    $o_receive_name = '';
    $o_send_id = '';
    $o_order_id = '';
    foreach ($res as $key => $value) {
        if($value['order_id'] == '0'){
            continue;
        }
        $now_send_id = $value['send_id'];

        // 如果是合单
        if(strstr($now_send_id, 'H') == true){
            // $k = all_total_money - sum(point) -sum(cheap);
            // 查询该订单的总积分和总优惠券
            $sql = "SELECT SUM(point) as sum_point,SUM(cheap) as sum_cheap FROM history_send WHERE send_id = '{$now_send_id}'";
            $res = $db->getOne($sql);
            $k = $value['all_total_money'] - $res['sum_point'] - $res['sum_cheap'];
        }else{
            // 单订单
            $k = $value['all_total_money'] - $value['point'] - $value['cheap'];
        }        

        // 依照发货ID计算的有：运费、支付金额
        if($value['send_id'] == $o_send_id){
            $value['ems_money'] = '';
            $value['bill'] = '';
            // 如果亚马逊 支付金额根据send_id计算
            $k = '';
        }else{
            $o_send_id = $value['send_id'];
        }

        // 依照订单ID计算的有：名字、税、积分、优惠券、总金额
        if($value['order_id'] == $o_order_id){
            $value['who_name'] = '';
            $value['receive_name'] = '';
            $value['tax'] = '';
            $value['point'] = '';
            $value['cheap'] = '';
        }else{
            $o_order_id = $value['order_id'];
        }
        // 替换[]
        $value['who_name'] = preg_replace('/\[.*?\]/', '', $value['who_name']);
        $value['receive_name'] = preg_replace('/\[.*?\]/', '', $value['receive_name']);
        // $value['express_day'] = str_replace('-', '', $value['express_day']);
        // $value['express_day'] = date('Y-m-d',strtotime($value['express_day']))

        $objSheet->setCellValue("A".$j,$value['who_name'])
                ->setCellValue("B".$j,$value['receive_name'])
                ->setCellValueExplicit("C".$j,$value['goods_code'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("D".$j,$value['out_num'])
                ->setCellValue("E".$j,$value['unit_price'])
                ->setCellValue("F".$j,$value['ems_money'])
                ->setCellValue("G".$j,$value['bill'])
                ->setCellValue("H".$j,$value['tax'])
                ->setCellValue("I".$j,$value['point'])
                ->setCellValue("J".$j,$value['cheap'])
                ->setCellValue("K".$j,$k)
                ->setCellValue("L".$j,$value['express_day'])
                // ->setCellValue("L".$j,$value['express_day'],PHPExcel_Shared_Date::PHPToExcel(time()))
                // ->setCellValue("L".$j, $value['express_day'],PHPExcel_Shared_Date::ExcelToPHP(time()))
                // ->setCellValue($value['express_day'],PHPExcel_Shared_Date::PHPToExcel( gmmktime(0,0,0,date(‘m’,$j),date(‘d’,$j),date(‘Y’,$j)) ))
                // PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
                // ->setCellValue('L'.$j, '=DATEVALUE(A'.$value['express_day'].')')
                // ->setFormatCode("L".$j,$value['express_day'],PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD)  
                // gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value))
                // ->$objPHPExcel->getActiveSheet()->getStyle($str)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15)
                ->setCellValue("M".$j,'新規')
                ->setCellValue("N".$j,$value['holder'])
                ->setCellValue("O".$j,$value['store_name'])
                ->setCellValueExplicit("P".$j,$value['oms_order_express_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("Q".$j,$value['pack_id'])
                ->setCellValueExplicit("R".$j,$value['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
                
                // ->setCellValue("D".$j,$value['sku'])
                // ->setCellValue("Q".$j,$value['receive_code'])
                // ->setCellValue("R".$j,$value['receive_phone'])
                // ->setCellValue("T".$j,$value['who_email']);
        $j++;
        
    }
    // $objSheet->getStyle('L1:L100')->getNumberFormat()->setFormatCode(PHPExcel_Cell_DataType::FORMAT_DATE_YYYYMMDD2);
    // $worksheet->getStyle($col_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
          // ->setFormatCode('yyyy/mm/dd');

	$objSheet->setTitle($t_title.$now_time);//表名
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/sell_detail_table.xlsx");   //保存在服务器
    echo "ok";
}