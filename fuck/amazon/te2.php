<?php
require_once("../header.php");
// http://oms.cc/fuck/amazon/te2.php?list_orders=gtx-amazon&station=amazon
$dir = dirname(__FILE__);

if(isset($_GET['list_orders'])){
 	$store = $_GET['list_orders'];
 	$station = strtolower($_GET['station']);


 	$syn_day = date('Y-m-d');	//同步日期
 	//所有oms_has状态变成0
 	$sql = "UPDATE amazon_response_list SET oms_has_me = '0'";
    $res = $db->execute($sql);

 	//获取店铺配置
 	$sql = "SELECT * FROM conf_Amazon WHERE store_name = '{$store}'";
    $res = $db->getOne($sql);
    $awsaccesskeyid = $res['awsaccesskeyid'];
    $sellerid = $res['sellerid'];
    $signatureversion = $res['signatureversion'];
    $secret = $res['secret'];
    $marketplaceid_id_1 = $res['marketplaceid_id_1'];

 	$param = array();
	$param['AWSAccessKeyId']   		= $awsaccesskeyid;			//*
	$param['SellerId']         		= $sellerid;					//*
	$param['SignatureVersion'] 		= $signatureversion; 								//*
	$param['MarketplaceId.Id.1']    = $marketplaceid_id_1;			//ListOrders
	$secret = $secret;				//*
	$param['Action']           		= 'ListOrders';						//* 返回订单
	$param['SignatureMethod']  		= 'HmacSHA256';						//*   
	$param['Timestamp']        		= gmdate("Y-m-d\TH:i:s\Z", time());	//*
	$param['Version']          		= '2013-09-01'; 					//*
	$param['OrderStatus.Status.1']    = 'Unshipped';			//ListOrders
	$param['OrderStatus.Status.2']    = 'PartiallyShipped';		//ListOrders
	$param['CreatedAfter']  = '2016-12-01T00:30:00Z';	//传值
	$url = array();
	foreach ($param as $key => $val) {
	    $key = str_replace("%7E", "~", rawurlencode($key));
	    $val = str_replace("%7E", "~", rawurlencode($val));
	    $url[] = "{$key}={$val}";
	}
	sort($url);

	$arr   = implode('&', $url);

	$sign  = 'GET' . "\n";
	$sign .= 'mws.amazonservices.jp' . "\n";
	$sign .= '/Orders/2013-09-01' . "\n";
	$sign .= $arr;

	$signature = hash_hmac("sha256", $sign, $secret, true);
	$signature = urlencode(base64_encode($signature));
	$link  = "https://mws.amazonservices.jp/Orders/2013-09-01?";
	$link .= $arr . "&Signature=" . $signature;
// echo($link);echo '<hr>'; //for debug
	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);

	function xmlToArray($xml)
	{    
	    //禁止引用外部xml实体
	    libxml_disable_entity_loader(true);
	    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
	    return $values;
	}

	$amazon_orders = xmlToArray($response);

	$arr =  $amazon_orders['ListOrdersResult'];
	$arr2 = $arr['Orders'];
	$count_order = count($arr2['Order']);

	//初始化数
	$insert_count = 0;
	$has_count = 0;
	for($i=0;$i<$count_order;$i++){
		//获取订单信息
		$order_id = $arr2['Order'][$i]['AmazonOrderId'];
		// 查询是否存在此订单
		// $sql = "SELECT * from amazon_response_list WHERE order_id = '{$order_id}'";
		// $res = $db->getOne($sql);

	    // if(empty($res)){
	        @$latest_ship_date = $arr2['Order'][$i]['LatestShipDate'];
			@$order_type = $arr2['Order'][$i]['OrderType'];
			@$purchase_date = $arr2['Order'][$i]['PurchaseDate'];
		echo	@$payment_method = $arr2['Order'][$i]['PaymentExecutionDetail']['PaymentExecutionDetailItem']['PaymentMethod'];
			if($payment_method == 'GC'){
				
			}
			@$pay_money = $arr2['Order'][$i]['PaymentExecutionDetail']['PaymentExecutionDetailItem']['Payment']['Amount'];
			@$buyer_email = $arr2['Order'][$i]['BuyerEmail'];
			// @$last_update_date = $arr2['Order'][$i]['LastUpdateDate'];
			@$buyer_name = $arr2['Order'][$i]['BuyerName'];
			@$order_total_currency = $arr2['Order'][$i]['OrderTotal']['CurrencyCode'];
			@$order_total_money = $arr2['Order'][$i]['OrderTotal']['Amount'];
			@$phone = $arr2['Order'][$i]['ShippingAddress']['Phone'];
			if(@$phone == ''){

			}else{
				//格式化电话号码
				$phone = str_replace("-","",$phone);	//替换-
				$phone = str_replace(" ","",$phone);	//替换空格
				//计算电话长度
				$phone_len = strlen($phone);
				if($phone_len == 11 or $phone_len == 10){
					$re3 = substr($phone,-4,4);//从后往前截取4位
					$re2 = substr($phone,-8,4);//从后往前截取4位
					$re1 = str_replace($re2.$re3, '', $phone);//截取前几位
					$phone = $re1.'-'.$re2.'-'.$re3;
				}else{
					$phone = '';
				}
			// }

		echo	@$receive_name = $arr2['Order'][$i]['ShippingAddress']['Name'];
			@$country = $arr2['Order'][$i]['ShippingAddress']['CountryCode'];
			@$post_code = $arr2['Order'][$i]['ShippingAddress']['PostalCode'];
			@$address0 = $arr2['Order'][$i]['ShippingAddress']['StateOrRegion'];
			@$address1 = $arr2['Order'][$i]['ShippingAddress']['AddressLine1'];
			@$address2 = $arr2['Order'][$i]['ShippingAddress']['AddressLine2'];
			@$address3 = $arr2['Order'][$i]['ShippingAddress']['AddressLine3'];
			@$address = $address0.$address1.$address2.$address3;
echo '<hr>';
			//sql
			// $sql = "INSERT INTO amazon_response_list(store,syn_day,latest_ship_date,order_type,purchase_date,payment_method,pay_money,buyer_email,order_id,buyer_name,order_total_currency,order_total_money,phone,receive_name,country,post_code,address,send_id,order_line,express_company,send_method) VALUES ('{$store}','{$syn_day}','{$latest_ship_date}','{$order_type}','{$purchase_date}','{$payment_method}','{$pay_money}','{$buyer_email}','{$order_id}','{$buyer_name}','{$order_total_currency}','{$order_total_money}','{$phone}','{$receive_name}','{$country}','{$post_code}','{$address}','ready','0','','')";
			// $res = $db->execute($sql);

			usleep(50000);
			$insert_count = $insert_count + 1;
	    }else{
	    	// $sql = "UPDATE amazon_response_list SET oms_has_me = 'has' WHERE order_id = '{$order_id}'";
	    	// $res = $db->execute($sql);
	    	$has_count = $has_count + 1;
	    	usleep(50000);
	    	continue;
	    }
	}

}