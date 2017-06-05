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
    $sql = "DELETE FROM amazon_express WHERE u_num = '{$u_num}'";
    $res = $db->execute($sql);

    $sql = "INSERT INTO amazon_express (amazon_order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,u_num) SELECT order_id,express_company,send_method,oms_order_express_num,buy_method,express_day,store_name,over_upload,over_mail,$u_num FROM history_send WHERE express_day BETWEEN '{$s_date}' AND '{$e_date}' AND store_name = '{$store}'";
    $res = $db->execute($sql);

    $sql = "SELECT * FROM amazon_express WHERE u_num = '{$u_num}'";
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
            ->setCellValue("B1","亚马逊订单号")
            ->setCellValue("C1","快递公司")
            ->setCellValue("D1","配送方式")
            ->setCellValue("E1","快递单号")
            ->setCellValue("F1","支付方式")
            ->setCellValue("G1","快递日期");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:G')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(14);//单元格宽
    $objSheet->getColumnDimension('B')->setWidth(30);//单元格宽
    $objSheet->getColumnDimension('C')->setWidth(10);//单元格宽
    $objSheet->getColumnDimension('D')->setWidth(10);//单元格宽
    $objSheet->getColumnDimension('E')->setWidth(20);//单元格宽
    $objSheet->getColumnDimension('F')->setWidth(20);//单元格宽
    $objSheet->getColumnDimension('G')->setWidth(12);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT * FROM amazon_express WHERE u_num = '{$u_num}' AND amazon_order_id in ($my_checked_items)";
    $res = $db->getAll($sql);
    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['store_name'])
                ->setCellValue("B".$j,$value['amazon_order_id'])
                ->setCellValue("C".$j,$value['express_company'])
                ->setCellValue("D".$j,$value['send_method'])
                
                ->setCellValueExplicit("E".$j,$value['oms_order_express_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("F".$j,$value['buy_method'])
                ->setCellValue("G".$j,$value['express_day']);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/amz_uploads_express.xlsx");   //保存在服务器
    echo "ok";
}

//发送订单快递
if(isset($_POST['amz_send_express'])){
    $store = $_POST['amz_send_express'];
    $my_checked_items = $_POST['my_checked_items'];

    //获取店铺配置
    $sql = "SELECT * FROM conf_Amazon WHERE store_name = '{$store}'";
    $res = $db->getOne($sql);
    $awsaccesskeyid = $res['awsaccesskeyid'];
    $sellerid = $res['sellerid'];
    $signatureversion = $res['signatureversion'];
    $secret = $res['secret'];
    $marketplaceid_id_1 = $res['marketplaceid_id_1'];

    $param = array();
    $param['AWSAccessKeyId']        = $awsaccesskeyid;          //*
    $param['SellerId']              = $sellerid;                    //*
    $param['SignatureVersion']      = $signatureversion;                                //*
    $secret = $secret;              //*
    $param['Action']                = 'SubmitFeed';                     //* 上传数据
    $param['FeedType']              = '_POST_ORDER_FULFILLMENT_DATA_';
    $param['SignatureMethod']       = 'HmacSHA256';                     //*   
    // $param['Timestamp']             = gmdate("Y-m-d\TH:i:s\Z", time()); //*
    $param['Timestamp']             = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    $param['Version']               = '2009-01-01';                     //*
    $param['MarketplaceId.Id.1']    = $marketplaceid_id_1;          //ListOrders
    $param['PurgeAndReplace']       = 'false';

	$url = array();
	foreach ($param as $key => $val) {
	    $key = str_replace("%7E", "~", rawurlencode($key));
	    $val = str_replace("%7E", "~", rawurlencode($val));
	    $url[] = "{$key}={$val}";
	}

	//查询快递列表
	$sql = "SELECT * FROM amazon_express WHERE u_num = '{$u_num}' AND amazon_order_id in ($my_checked_items)";
    $res = $db->getAll($sql);
    $amazon_items = '';
    $message_id = 1;
    foreach ($res as $value) {
    	//是否带引
    	if($value['buy_method'] == 'DirectPayment'){
    		$item_order = '
			<Message>
            <MessageID>'.$message_id.'</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>'.$value['amazon_order_id'].'</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$value['express_company'].'</CarrierName>
            <ShippingMethod>'.$value['send_method'].'</ShippingMethod>
            <ShipperTrackingNumber>'.$value['oms_order_express_num'].'</ShipperTrackingNumber>
            </FulfillmentData>
            <CODCollectionMethod>DirectPayment</CODCollectionMethod>
            </OrderFulfillment>
            </Message>';
    	}else{
    		$item_order = '
			<Message>
            <MessageID>'.$message_id.'</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>'.$value['amazon_order_id'].'</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$value['express_company'].'</CarrierName>
            <ShippingMethod>'.$value['send_method'].'</ShippingMethod>
            <ShipperTrackingNumber>'.$value['oms_order_express_num'].'</ShipperTrackingNumber>
            </FulfillmentData>
            </OrderFulfillment>
            </Message>';
    	}
    	$message_id = $message_id + 1;
    	$amazon_items = $amazon_items.$item_order;
    }
    $amazon_feed = '<?xml version="1.0" encoding="Shift_JIS"?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    		xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
 			<Header>
    			<DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>'.$sellerid.'</MerchantIdentifier>
          	</Header>
            <MessageType>OrderFulfillment</MessageType>'.$amazon_items.'</AmazonEnvelope>';

	sort($url);

    // 标记状态
    $sql = "UPDATE amazon_express SET over_upload = 1 WHERE amazon_order_id IN ($my_checked_items)";
    $res = $db->execute($sql);
    $sql = "UPDATE history_send SET over_upload = 1 WHERE order_id IN ($my_checked_items)";
    $res = $db->execute($sql);
echo $amazon_feed;die;

	$arr   = implode('&', $url);

	$sign  = 'POST' . "\n";
	$sign .= 'mws.amazonservices.jp' . "\n";
	$sign .= '/Feeds/'.$param['Version'].'' . "\n";
	$sign .= $arr;

	$signature = hash_hmac("sha256", $sign, $secret, true);
	$httpHeader     =   array();
    $httpHeader[]   =   'Transfer-Encoding: chunked';
    $httpHeader[]   =   'Content-Type: application/xml';
    $httpHeader[]   =   'Content-MD5: ' . base64_encode(md5($amazon_feed, true));
    $httpHeader[]   =   'Expect:';
    $httpHeader[]   =   'Accept:';              

	$signature = urlencode(base64_encode($signature));
	$link  = "https://mws.amazonservices.jp/Feeds/".$param['Version']."?";
	$link .= $arr . "&Signature=" . $signature;
	// echo($link);echo '<hr>'; //for debug

	$ch = curl_init($link);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $amazon_feed); 
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);


	// echo('<p>' . $response . '</p>');


echo 'ok';
}