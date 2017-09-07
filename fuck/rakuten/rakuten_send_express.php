<?php
require_once("../header.php");
$dir = dirname(__FILE__);

set_time_limit(30);

//查询快递列表
if(isset($_GET['get_express_list'])){
    $store = $_GET['store'];
    $s_date = $_GET['s_date'];
    $e_date = $_GET['e_date'];

    // 清空快递列表
    $sql = "DELETE FROM rakuten_express WHERE u_num = '{$u_num}'";
    $res = $db->execute($sql);

    $sql = "INSERT INTO rakuten_express (rakuten_order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,u_num) SELECT order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,$u_num FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' AND store_name = '{$store}'";
    $res = $db->execute($sql);

    $sql = "SELECT * FROM rakuten_express WHERE u_num = '{$u_num}' GROUP BY rakuten_order_id";
    $res = $db->getAll($sql);

    echo json_encode($res);
}

// 下载快递单
if(isset($_POST['down_express_xlsx'])){
    $my_checked_items = $_POST['my_checked_items'];

    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('上传快递单@'.$now_time);//表名
    $objSheet->setCellValue("A1","受注番号")
            ->setCellValue("B1","お荷物伝票番号")
            ->setCellValue("C1","配送会社");    //表头值

    //SQL
    $sql = "SELECT * FROM rakuten_express WHERE u_num = '{$u_num}' AND rakuten_order_id in ($my_checked_items) GROUP BY rakuten_order_id";
    $res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
    	$express_company = $value['express_company'];
    	if($express_company == 'ヤマト運輸'){
    		$express_company = '1001';
    	}
    	if($express_company == '佐川急便'){
    		$express_company = '1002';
    	}
        $objSheet->setCellValue("A".$j,$value['rakuten_order_id'])
                ->setCellValueExplicit("B".$j,$value['oms_order_express_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("C".$j,$express_company);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/RkutenExpress.xlsx");   //保存在服务器

    $filename = $dir."/../../down/RkutenExpress.xlsx";
	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	$objPHPExcel = $objReader->load($filename);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
	$objWriter->setUseBOM(true); //设置utf-8
	$objWriter->save(str_replace('.xlsx', '.csv',$filename));
	echo "ok";
}