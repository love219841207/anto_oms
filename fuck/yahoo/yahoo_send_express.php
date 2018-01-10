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
    $sql = "DELETE FROM yahoo_express WHERE u_num = '{$u_num}'";
    $res = $db->execute($sql);

    $sql = "INSERT INTO yahoo_express (yahoo_order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,u_num) SELECT order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,$u_num FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' AND store_name = '{$store}'";
    $res = $db->execute($sql);

    $sql = "SELECT * FROM yahoo_express WHERE u_num = '{$u_num}' GROUP BY yahoo_order_id";
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
    $objSheet->setTitle('下载快递单@'.$now_time);//表名
    $objSheet->setCellValue("A1","OrderId")
            ->setCellValue("B1","OrderStatus")
            ->setCellValue("C1","ShipMethod")
            ->setCellValue("D1","ShipInvoiceNumber1")
            ->setCellValue("E1","ShipInvoiceNumber2")
            ->setCellValue("F1","ShipUrl")
            ->setCellValue("G1","ShipDate")
            ->setCellValue("H1","ShipCharge")
            ->setCellValue("I1","PayCharge")
            ->setCellValue("J1","GiftWrapCharge")
            ->setCellValue("K1","Discount")
            ->setCellValue("L1","ShipStatus")
            ->setCellValue("M1","PayStatus")
            ->setCellValue("N1","StoreStatus")
            ->setCellValue("O1","Suspect");    //表头值

    //SQL
    $sql = "SELECT * FROM yahoo_express WHERE u_num = '{$u_num}' AND yahoo_order_id in ($my_checked_items) GROUP BY yahoo_order_id";
    $res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
        $url = '';
        if($value['express_company'] == '佐川急便'){
            $url = 'http://k2k.sagawa-exp.co.jp/p/sagawa/web/okurijoinput.jsp';
        }
        if($value['express_company'] == 'ヤマト運輸'){
            $url = 'http://toi.kuronekoyamato.co.jp/cgi-bin/tneko';
        }
        $arr = explode('-', $value['yahoo_order_id']);

        $objSheet->setCellValue("A".$j,$arr['1'])
                 ->setCellValue("D".$j,$value['oms_order_express_num'])
                 ->setCellValue("F".$j,$url)
                 ->setCellValue("G".$j,$value['express_day'])
                 ->setCellValue("L".$j,'3');
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/yahooExpress.xlsx");   //保存在服务器

    $filename = $dir."/../../down/yahooExpress.xlsx";
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel = $objReader->load($filename);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objWriter->setUseBOM(true); //设置utf-8
    $objWriter->save(str_replace('.xlsx', '.csv',$filename));
    echo "ok";
}