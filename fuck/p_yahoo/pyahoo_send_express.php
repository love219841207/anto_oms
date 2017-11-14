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
    $sql = "DELETE FROM p_yahoo_express WHERE u_num = '{$u_num}'";
    $res = $db->execute($sql);

    $sql = "INSERT INTO p_yahoo_express (pyahoo_order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,u_num) SELECT order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,$u_num FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' AND store_name = '{$store}'";
    $res = $db->execute($sql);

    // 更新顾客ID到over_upload
    $sql = "UPDATE p_yahoo_express express,p_yahoo_response_list list SET express.over_upload = list.who_id WHERE list.order_id = express.pyahoo_order_id";
    $res = $db->execute($sql);

    $sql = "SELECT * FROM p_yahoo_express WHERE u_num = '{$u_num}' GROUP BY pyahoo_order_id";
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
    $objSheet->setCellValue("A1","店铺")
            ->setCellValue("B1","雅虎ID")
            ->setCellValue("C1","拍卖订单号")
            ->setCellValue("D1","快递公司")
            ->setCellValue("E1","快递单号")
            ->setCellValue("F1","配送方式")
            ->setCellValue("G1","快递日期");    //表头值

    //SQL
    $sql = "SELECT * FROM p_yahoo_express WHERE u_num = '{$u_num}' AND pyahoo_order_id in ($my_checked_items) GROUP BY pyahoo_order_id";
    $res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['store_name'])
                ->setCellValue("B".$j,$value['over_upload'])    //这里是顾客ID
                ->setCellValue("C".$j,$value['pyahoo_order_id'])
                ->setCellValue("D".$j,$value['express_company'])
                ->setCellValue("E".$j,$value['oms_order_express_num'])
                ->setCellValue("F".$j,$value['send_method'])
                ->setCellValue("G".$j,$value['express_day']);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/PyahooExpress.xlsx");   //保存在服务器
    echo "ok";
}