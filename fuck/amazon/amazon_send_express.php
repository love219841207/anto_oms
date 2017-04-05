<?php
require_once("../header.php");
require_once("../amazon_conf.php");
$dir = dirname(__FILE__);

set_time_limit(30);

//查询快递列表
if(isset($_GET['amazon_express'])){
	$sql = "SELECT * FROM amazon_express WHERE send_status='0'";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

//清空快递列表[测试用]
if(isset($_GET['truncate_express'])){
	$sql = "truncate amazon_express;";
    $res = $db->execute($sql);
    echo 'ok';
}

//清空订单[测试用]
if(isset($_GET['truncate_orders'])){
    $sql = "truncate amazon_response_info;";
    $res = $db->execute($sql);
    $sql = "truncate amazon_response_list;";
    $res = $db->execute($sql);
    echo 'ok';
}

//发送订单快递
if(isset($_GET['send_express'])){

 	$param = array();
	$param['AWSAccessKeyId']   		= AWSACCESSKEYID;			//*
	$param['SellerId']         		= SELLERID;					//*
	$param['SignatureVersion'] 		= SIGNATUREVERSION; 								//*
	$secret = SECRET;				//*
	$param['Action']           		= 'SubmitFeed';						//* 上传数据
	$param['FeedType']         		= '_POST_ORDER_FULFILLMENT_DATA_';
	$param['SignatureMethod']  		= 'HmacSHA256';						//*   
	// $param['Timestamp']        		= gmdate("Y-m-d\TH:i:s\Z", time());	//*
	$param['Timestamp']        		= gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
	$param['Version']          		= '2009-01-01'; 					//*
	$param['MARKETPLACEID_ID_1']    = MARKETPLACEID_ID_1; 					//*
	$param['PurgeAndReplace']    	= 'false';

	$url = array();
	foreach ($param as $key => $val) {
	    $key = str_replace("%7E", "~", rawurlencode($key));
	    $val = str_replace("%7E", "~", rawurlencode($val));
	    $url[] = "{$key}={$val}";
	}

	//查询快递列表
	$sql = "SELECT * FROM amazon_express WHERE send_status='0'";
    $res = $db->getAll($sql);
    $amazon_items = '';
    $message_id = 1;
    foreach ($res as $value) {
    	//是否带引
    	if($value['is_money'] == 'DirectPayment'){
    		$item_order = '
			<Message>
            <MessageID>'.$message_id.'</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>'.$value['amazon_order_id'].'</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$value['express_name'].'</CarrierName>
            <ShippingMethod>'.$value['express_method'].'</ShippingMethod>
            <ShipperTrackingNumber>'.$value['express_num'].'</ShipperTrackingNumber>
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
            <CarrierName>'.$value['express_name'].'</CarrierName>
            <ShippingMethod>'.$value['express_method'].'</ShippingMethod>
            <ShipperTrackingNumber>'.$value['express_num'].'</ShipperTrackingNumber>
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
                <MerchantIdentifier>'.SELLERID.'</MerchantIdentifier>
          	</Header>
            <MessageType>OrderFulfillment</MessageType>'.$amazon_items.'</AmazonEnvelope>';

	sort($url);

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