<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 同步亚马逊订单列表
if(isset($_GET['list_orders'])){
 	$store = $_GET['list_orders'];
 	$station = strtolower($_GET['station']);

	//日志
	$do = '[START] 同步订单列表：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store,'-');

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
	$param['FulfillmentChannel.Channel.1']  = 'MFN';	//自己配送
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

	$xml = simplexml_load_string($response);
	
	$xxx =	$xml ->ListOrdersResult->Orders;
	// print_r($xxx);
// echo '<hr>';

	//初始化数
	$insert_count = 0;
	$has_count = 0;
	$count_order = count($xxx->Order);

	foreach ($xxx->children() as $child){  
        $LatestShipDate = $child->LatestShipDate;  
        $OrderType = $child->OrderType;  
        $PurchaseDate = $child->PurchaseDate;  
        $BuyerEmail = $child->BuyerEmail;  
        $BuyerName = $child->BuyerName;  
        $AmazonOrderId = $child->AmazonOrderId;  
        $LastUpdateDate = $child->LastUpdateDate;  
        $PaymentMethodDetails = $child->PaymentMethodDetails->PaymentMethodDetail;   #支付方式
        $OrderTotal = $child->OrderTotal->CurrencyCode;  
        $OrderTotal = $child->OrderTotal->Amount;  
        $PaymentMethod = $child->PaymentMethod;  
        $payment_tiems = $child->PaymentExecutionDetail->PaymentExecutionDetailItem;
	 	$payment_count = count($payment_tiems);

		// 查询是否存在此订单
		$sql = "SELECT * from amazon_response_list WHERE order_id = '{$AmazonOrderId}'";
		$res = $db->getOne($sql);

	if(empty($res)){
		if($payment_count == 0){
			
		}
		if($payment_count == 1){
			$PaymentMethod0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->PaymentMethod;  
	        $Amount0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->Payment->Amount;  
		}
		if($payment_count == 2){
			$PaymentMethod0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->PaymentMethod;  
	        $Amount0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->Payment->Amount;  
	        $PaymentMethod1 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[1]->PaymentMethod;  
	        $Amount1 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[1]->Payment->Amount;  
		}
		if($payment_count == 3){
			$PaymentMethod0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->PaymentMethod;  
	        $Amount0 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem->Payment->Amount;  
	        $PaymentMethod1 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[1]->PaymentMethod;  
	        $Amount1 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[1]->Payment->Amount;  
			$PaymentMethod2 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[2]->PaymentMethod;  
	        $Amount2 = $child->PaymentExecutionDetail->PaymentExecutionDetailItem[2]->Payment->Amount;  
		}

		$point = '0';
		$GC = '0';
		$cod = '0';
	     
	    if($payment_count == 0){
			
		}
		if($payment_count == 1){
			if($PaymentMethod0 == 'PointsAccount'){
	    			$point = $Amount0;
		    }
		    if($PaymentMethod0 == 'GC'){
		    		$GC = $Amount0;
		    }
		    if($PaymentMethod0 == 'COD'){
		    	$cod = $Amount0;
		    }
		}
		if($payment_count == 2){
			if($PaymentMethod0 == 'PointsAccount'){
	    			$point = $Amount0;
		    }
		    if($PaymentMethod0 == 'GC'){
		    		$GC = $Amount0;
		    }
		    if($PaymentMethod0 == 'COD'){
		    		$cod = $Amount0;
		    }
		    if($PaymentMethod1 == 'PointsAccount'){
	    			$point = $Amount1;
		    }
		    if($PaymentMethod1 == 'GC'){
		    	$GC = $Amount1;
		    }
		    if($PaymentMethod1 == 'COD'){
		    		$cod = $Amount1;
		    }
		}
		if($payment_count == 3){
			if($PaymentMethod0 == 'PointsAccount'){
	    		$point = $Amount0;
		    }
		    if($PaymentMethod0 == 'GC'){
		    	$GC = $Amount0;
		    }
		    if($PaymentMethod0 == 'COD'){
		    	$cod = $Amount0;
		    }
		    if($PaymentMethod1 == 'PointsAccount'){
	    		$point = $Amount1;
		    }
		    if($PaymentMethod1 == 'GC'){
		    	$GC = $Amount1;
		    }
		    if($PaymentMethod1 == 'COD'){
		    	$cod = $Amount1;
		    }
		    if($PaymentMethod2 == 'PointsAccount'){
	    		$point = $Amount2;
		    }
		    if($PaymentMethod2 == 'GC'){
		    	$GC = $Amount2;
		    }
		    if($PaymentMethod2 == 'COD'){
		    	$cod = $Amount2;
		    }
		}
    
	    $PostalCode = $child->ShippingAddress->PostalCode;  
   	 	$address0 = $child->ShippingAddress->StateOrRegion;
    	$address1 = $child->ShippingAddress->AddressLine1; 
    	$address2 = $child->ShippingAddress->AddressLine2;
	    $address3 = $child->ShippingAddress->AddressLine3;
	    $address = $address0.$address1.$address2.$address3;

		@$phone = $child->ShippingAddress->Phone;
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
		}
		$country = $child->ShippingAddress->CountryCode;
	 	$order_total_currency = $child->OrderTotal->CurrencyCode; 
		$order_total_money = $child->OrderTotal->Amount;
		$receive_name = $child->ShippingAddress->Name;

		$sql = "INSERT INTO amazon_response_list(
			store,	#店铺名
			syn_day,	#同步日期
			latest_ship_date,	#最后发货日期
			order_type,	#订单类型
			purchase_date,	#付款日期
			payment_method,	#付款方式
			pay_money,	#待付款
			buyer_email,	#邮箱
			order_id,	#订单号
			buyer_name,	#购买人
			order_total_currency,	#货币
			order_total_money,	#总价
			phone,	#手机
			receive_name,	#收件人
			country,	#国家
			post_code,	#邮编
			address,	#地址
			send_id,	#发货id
			order_line,	#order_line
			express_company,	#发货公司
			send_method,	#发货方式
			coupon,	#优惠券
			points	#积分
			) VALUES (
			'{$store}',
			'{$syn_day}',
			'{$LatestShipDate}',
			'{$OrderType}',
			'{$PurchaseDate}',
			'{$PaymentMethod}',
			'{$cod}',
			'{$BuyerEmail}',
			'{$AmazonOrderId}',
			'{$BuyerName}',
			'{$order_total_currency}',
			'{$order_total_money}',
			'{$phone}',
			'{$receive_name}',
			'{$country}',
			'{$PostalCode}',
			'{$address}',
			'ready',
			'0',
			'',
			'',
			'{$GC}',
			'{$point}')";

			$res = $db->execute($sql);

			usleep(50000);
			$insert_count = $insert_count + 1;
	    }else{
	    	$sql = "UPDATE amazon_response_list SET oms_has_me = 'has' WHERE order_id = '{$AmazonOrderId}'";
	    	$res = $db->execute($sql);
	    	$has_count = $has_count + 1;
	    	usleep(50000);
	    	continue;
	    }
	}
	//send_id变成amz+id
	$sql = "UPDATE amazon_response_list SET send_id = concat('amz',id) WHERE send_id = 'ready'";
	$res = $db->execute($sql);

	//final_red
	$final_res['status'] = 'list_ok';	//状态
	$final_res['count_order'] = $count_order;	//获取总数
	$final_res['insert_count'] = $insert_count;	//实际插入数
	$final_res['has_count'] = $has_count;	//实际插入数

	//日志
	$do = '[END] 同步订单列表：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store,'-');

	echo json_encode($final_res);
}

// 获取亚马逊订单详单
if(isset($_GET['get_order_info'])){
 	$store = $_GET['get_order_info'];
 	$station = strtolower($_GET['station']);

 	//日志
	$do = '[START] 同步订单详单：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store,'-');

 	// 搜索出需要获取详情的订单
 	$sql = "SELECT order_id FROM amazon_response_list WHERE store = '{$store}' AND oms_order_info_status='0'";
 	$res = $db->getAll($sql);
 	$arr_order_id = array();
 	foreach ($res as $value) {
 		$order_id = $value['order_id'];
 		//查询是否存在
 		$sql = "SELECT * from amazon_response_info WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);
	    if(empty($res)){
	    	array_push($arr_order_id,$order_id);
	    }else{
	    	//如果存在，更新order_line
	    	$sql = "UPDATE amazon_response_list SET order_line = '1',oms_order_info_status = 'ok' WHERE order_id = '{$order_id}'";
	    	$res = $db->execute($sql);
	    }
 	}

	//获取店铺配置
 	$sql = "SELECT * FROM conf_Amazon WHERE store_name = '{$store}'";
    $res = $db->getOne($sql);
    $awsaccesskeyid = $res['awsaccesskeyid'];
    $sellerid = $res['sellerid'];
    $signatureversion = $res['signatureversion'];
    $secret = $res['secret'];
    $marketplaceid_id_1 = $res['marketplaceid_id_1'];

 	$arr_count = count($arr_order_id);

	for($i=0;$i<$arr_count;$i++){
		$param = array();
		$param['AWSAccessKeyId']   		= $awsaccesskeyid;			//*
		$param['SellerId']         		= $sellerid;					//*
		$param['SignatureVersion'] 		= $signatureversion; 								//*
		$secret = $secret;				//*
		$param['Action']           		= 'ListOrderItems';						//* 返回订单
		$param['SignatureMethod']  		= 'HmacSHA256';						//*   
		$param['Timestamp']        		= gmdate("Y-m-d\TH:i:s\Z", time());	//*
		$param['Version']          		= '2013-09-01'; 					//*
		$param['AmazonOrderId']    		= $arr_order_id[$i];			//ListOrders

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

		$ch = curl_init($link);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		// 当前时间戳
		$now_time = time();
		
		$xml = simplexml_load_string($response);
		$xxx =	$xml ->ListOrderItemsResult->OrderItems;
		foreach ($xxx->children() as $child){  
	        $goods_num = $child->QuantityOrdered;
			$goods_title = $child->Title;
			$promotion_discount = $child->PromotionDiscount->Amount;
			$sku = $child->SellerSKU;
			$shipping_tax = $child->ShippingTax->Amount;
			$gift_tax = $child->GiftWrapTax->Amount;
			$shipping_price = $child->ShippingPrice->Amount;
			$gift_price = $child->GiftWrapPrice->Amount;
			$item_price = $child->ItemPrice->Amount;
			$unit_price = $item_price/$goods_num;
			$item_tax = $child->ItemTax->Amount;
			$shipping_discount = $child->ShippingDiscount->Amount;
			@$cod_money = $child->CODFee->Amount;
			$sql = "INSERT INTO amazon_response_info(store,order_id,goods_num,goods_title,promotion_discount,sku,shipping_tax,gift_tax,shipping_price,gift_price,item_price,unit_price,item_tax,shipping_discount,cod_money,import_time) VALUES('{$store}','{$arr_order_id[$i]}','{$goods_num}','{$goods_title}','{$promotion_discount}','{$sku}','{$shipping_tax}','{$gift_tax}','{$shipping_price}','{$gift_price}','{$item_price}','{$unit_price}','{$item_tax}','{$shipping_discount}','{$cod_money}',{$now_time})";
			$res = $db->execute($sql);
			// usleep(50000);
	    }

		//更新状态已经获取完毕。更新order_line。
		$sql = "UPDATE amazon_response_list SET oms_order_info_status='ok',order_line = '1' where order_id='{$arr_order_id[$i]}'";
		$res = $db->execute($sql);
	}		
 	$final_res['status'] = 'info_ok';

 	//日志
	$do = '[END] 同步订单详单：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store,'-');

 	echo json_encode($final_res);
}