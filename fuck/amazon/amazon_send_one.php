<?php
require_once("../header.php");
require_once("../log.php");

$dir = dirname(__FILE__);

set_time_limit(30);
if(isset($_GET['send_one'])){
	$AmazonOrderID = $_GET['send_one'];
	$ShippingMethod = $_GET['ShippingMethod'];
	$ShipperTrackingNumber = $_GET['ShipperTrackingNumber'];
	$CarrierName = $_GET['CarrierName'];

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

	$amazon_feed='<?xml version="1.0" encoding="Shift_JIS"?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    		xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
 			<Header>
    			<DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>'.SELLERID.'</MerchantIdentifier>
          	</Header>
            <MessageType>OrderFulfillment</MessageType>
            <Message>
            <MessageID>1</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>'.$AmazonOrderID.'</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$CarrierName.'</CarrierName>
            <ShippingMethod>'.$ShippingMethod.'</ShippingMethod>
            <ShipperTrackingNumber>'.$ShipperTrackingNumber.'</ShipperTrackingNumber>
            </FulfillmentData>
            </OrderFulfillment>
            </Message>

			<Message>
            <MessageID>2</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>503-0433374-1761451</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$CarrierName.'</CarrierName>
            <ShippingMethod>'.$ShippingMethod.'</ShippingMethod>
            <ShipperTrackingNumber>400752872225</ShipperTrackingNumber>
            </FulfillmentData>
            </OrderFulfillment>
            </Message>

            <Message>
            <MessageID>3</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>250-3357494-3619837</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$CarrierName.'</CarrierName>
            <ShippingMethod>'.$ShippingMethod.'</ShippingMethod>
            <ShipperTrackingNumber>400752872424 </ShipperTrackingNumber>
            </FulfillmentData>
			<CODCollectionMethod>DirectPayment</CODCollectionMethod>
            </OrderFulfillment>
            </Message>

            <Message>
            <MessageID>4</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>249-5802941-4455846</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$CarrierName.'</CarrierName>
            <ShippingMethod>'.$ShippingMethod.'</ShippingMethod>
            <ShipperTrackingNumber>400752872951</ShipperTrackingNumber>
            </FulfillmentData>
			<CODCollectionMethod>DirectPayment</CODCollectionMethod>
            </OrderFulfillment>
            </Message>

            <Message>
            <MessageID>5</MessageID>
            <OrderFulfillment>
            <AmazonOrderID>250-3145307-5604664</AmazonOrderID>
            <FulfillmentDate>'.$param['Timestamp'].'</FulfillmentDate>
            <FulfillmentData>
            <CarrierName>'.$CarrierName.'</CarrierName>
            <ShippingMethod>'.$ShippingMethod.'</ShippingMethod>
            <ShipperTrackingNumber>400503113290</ShipperTrackingNumber>
            </FulfillmentData>
			<CODCollectionMethod>DirectPayment</CODCollectionMethod>
            </OrderFulfillment>
            </Message>
            </AmazonEnvelope>';

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
	echo($link);echo '<hr>'; //for debug

	$ch = curl_init($link);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $amazon_feed); 
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);


	echo('<p>' . $response . '</p>');


echo 'ok';
}
