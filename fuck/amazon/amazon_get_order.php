<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 1111111111111111111111111111111111111111  同步亚马逊订单开始 1111111111111111111111111111111111111111 
//获取亚马逊订单列表
if(isset($_GET['list_orders'])){
 	$store = $_GET['list_orders'];
 	$station = strtolower($_GET['station']);

 	//日志
	$do = '[START] 同步订单列表：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store);

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
		$amazon_order_id = $arr2['Order'][$i]['AmazonOrderId'];
		// 查询是否存在此订单
		$sql = "SELECT * from amazon_response_list WHERE amazon_order_id = '{$amazon_order_id}'";
		$res = $db->getOne($sql);

	    if(empty($res)){
	        @$latest_ship_date = $arr2['Order'][$i]['LatestShipDate'];
			@$order_type = $arr2['Order'][$i]['OrderType'];
			@$purchase_date = $arr2['Order'][$i]['PurchaseDate'];
			@$payment_method = $arr2['Order'][$i]['PaymentExecutionDetail']['PaymentExecutionDetailItem']['PaymentMethod'];
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
			}

			@$receive_name = $arr2['Order'][$i]['ShippingAddress']['Name'];
			@$country = $arr2['Order'][$i]['ShippingAddress']['CountryCode'];
			@$post_code = $arr2['Order'][$i]['ShippingAddress']['PostalCode'];
			@$address0 = $arr2['Order'][$i]['ShippingAddress']['StateOrRegion'];
			@$address1 = $arr2['Order'][$i]['ShippingAddress']['AddressLine1'];
			@$address2 = $arr2['Order'][$i]['ShippingAddress']['AddressLine2'];
			@$address = $address0.$address1.$address2;

			//sql
			$sql = "INSERT INTO amazon_response_list(store,syn_day,latest_ship_date,order_type,purchase_date,payment_method,pay_money,buyer_email,amazon_order_id,buyer_name,order_total_currency,order_total_money,phone,receive_name,country,post_code,address,send_id,order_line,express_company,send_method) VALUES ('{$store}','{$syn_day}','{$latest_ship_date}','{$order_type}','{$purchase_date}','{$payment_method}','{$pay_money}','{$buyer_email}','{$amazon_order_id}','{$buyer_name}','{$order_total_currency}','{$order_total_money}','{$phone}','{$receive_name}','{$country}','{$post_code}','{$address}','ready','0','','')";
			$res = $db->execute($sql);

			usleep(50000);
			$insert_count = $insert_count + 1;
	    }else{
	    	$sql = "UPDATE amazon_response_list SET oms_has_me = 'has' WHERE amazon_order_id = '{$amazon_order_id}'";
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
	oms_log($u_name,$do,'amazon_syn',$station,$store);

	echo json_encode($final_res);
}

//查询已存在订单号
if(isset($_GET['has_orders'])){
	$store = $_GET['has_orders'];
	$sql = "SELECT amazon_order_id FROM amazon_response_list WHERE oms_has_me = 'has' ORDER BY id DESC";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//获取亚马逊订单详单
if(isset($_GET['get_order_info'])){
 	$store = $_GET['get_order_info'];
 	$station = strtolower($_GET['station']);
 	
 	//日志
	$do = '[START] 同步订单详单：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store);

 	// 搜索出需要获取详情的订单
 	$sql = "SELECT amazon_order_id FROM amazon_response_list WHERE store = '{$store}' AND oms_order_info_status='0'";
 	$res = $db->getAll($sql);
 	$arr_order_id = array();
 	foreach ($res as $value) {
 		$amazon_order_id = $value['amazon_order_id'];
 		//查询是否存在
 		$sql = "SELECT * from amazon_response_info WHERE amazon_order_id = '{$amazon_order_id}'";
		$res = $db->getOne($sql);
	    if(empty($res)){
	    	array_push($arr_order_id,$amazon_order_id);
	    }else{
	    	//如果存在，更新order_line
	    	$sql = "UPDATE amazon_response_list SET order_line = '1',oms_order_info_status = 'ok' WHERE amazon_order_id = '{$amazon_order_id}'";
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
		
		$xml = simplexml_load_string($response);
		$xxx =	$xml ->ListOrderItemsResult->OrderItems;
		foreach ($xxx->children() as $child){  
	        $goods_num = $child->QuantityOrdered;
			$goods_title = $child->Title;
			$promotion_discount = $child->PromotionDiscount->Amount;
			$asin = $child->ASIN;
			$sku = $child->SellerSKU;
			$order_item_id = $child->OrderItemId;
			$shipping_tax = $child->ShippingTax->Amount;
			$gift_tax = $child->GiftWrapTax->Amount;
			$shipping_price = $child->ShippingPrice->Amount;
			$gift_price = $child->GiftWrapPrice->Amount;
			$item_price = $child->ItemPrice->Amount;
			$item_tax = $child->ItemTax->Amount;
			$shipping_discount = $child->ShippingDiscount->Amount;
			@$cod_money = $child->CODFee->Amount;
			$sql = "INSERT INTO amazon_response_info(store,amazon_order_id,goods_num,goods_title,promotion_discount,asin,sku,order_item_id,shipping_tax,gift_tax,shipping_price,gift_price,item_price,item_tax,shipping_discount,cod_money) VALUES('{$store}','{$arr_order_id[$i]}','{$goods_num}','{$goods_title}','{$promotion_discount}','{$asin}','{$sku}','{$order_item_id}','{$shipping_tax}','{$gift_tax}','{$shipping_price}','{$gift_price}','{$item_price}','{$item_tax}','{$shipping_discount}','{$cod_money}')";
			$res = $db->execute($sql);
			// usleep(50000);
	    }

		//更新状态已经获取完毕。更新order_line。
		$sql = "UPDATE amazon_response_list SET oms_order_info_status='ok',order_line = '1' where amazon_order_id='{$arr_order_id[$i]}'";
		$res = $db->execute($sql);
	}		
 	$final_res['status'] = 'info_ok';

 	//日志
	$do = '[END] 同步订单详单：'.$store;
	oms_log($u_name,$do,'amazon_syn',$station,$store);

 	echo json_encode($final_res);
}

// 1111111111111111111111111111111111111111  同步亚马逊订单结束 1111111111111111111111111111111111111111 



// 2222222222222222222222222222222222222222  亚马逊订2222222222222222222222222222222222222222 



//获取分页数
if(isset($_GET['get_pagesize'])){
	$get_pagesize = $_GET['get_pagesize'];
	$sql = "SELECT page_size FROM user_oms WHERE u_num = '{$_SESSION['oms_u_num']}'";
	$res = $db->getOne($sql);
	echo $res['page_size'];
}

// 分配快递公司
if(isset($_GET['express_company'])){
	$store = $_GET['express_company'];

	//	更新黑猫地址	（神奈川県，埼玉県，茨城県，群馬県，山梨県）
	$sql = "UPDATE amazon_response_list SET express_company = 'ヤマト運輸',send_method = '宅急便' WHERE address LIKE '%神奈川県%' OR address LIKE '%埼玉県%' OR address LIKE '%茨城県%' OR address LIKE '%群馬県%' OR address LIKE '%山梨県%' AND store = '{$store}' AND order_line = '1'";
	$res = $db->execute($sql);
	echo 'ok';
}


//一键合单
if(isset($_GET['onekey_common_order'])){
	$store = $_GET['onekey_common_order'];
	$sql = "
		UPDATE amazon_response_list a,
		(SELECT a.id FROM amazon_response_list a,
		(SELECT receive_name,count(id) as num
		FROM amazon_response_list WHERE store='{$store}' AND order_line = '2'
		group by receive_name,phone,post_code,buyer_email,address,payment_method
		having num>1) b
		WHERE a.receive_name = b.receive_name) b
		SET a.send_id = concat('H',a.phone)
		WHERE a.id = b.id";
	$res = $db->execute($sql);

	//order_line
	$sql = "UPDATE amazon_response_list SET order_line = '3' WHERE order_line = '2' AND store = '{$store}'";
	$res = $db->execute($sql);

	//查询所有合单号
	$sql = "SELECT send_id FROM amazon_response_list WHERE store='{$store}' AND send_id LIKE 'H%' AND order_line = '3' GROUP BY send_id";
	$res = $db->getAll($sql);

	$all_one = '';
	foreach ($res as $value) {
		$all_one = $all_one.'['.$value['send_id'].']';
	}

	// 日志
	if($all_one ==''){
		$do = '[合单]：本次无合单';
	}else{
		$do = '[合单]：'.$all_one;
	}
	oms_log($u_name,$do,'amazon_order');

	echo json_encode($res);
}

//查询合单列表
if(isset($_GET['list_common_order'])){
	$store = $_GET['list_common_order'];
	//查询所有合单号
	$sql = "SELECT send_id FROM amazon_response_list WHERE store='{$store}' AND send_id LIKE 'H%' AND order_line = '3' GROUP BY send_id";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//查询单个合单详情
if(isset($_GET['get_common_order'])){
	$send_id = $_GET['get_common_order'];
	$sql = "SELECT * FROM amazon_response_list WHERE send_id = '{$send_id}'";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//拆单
if(isset($_GET['break_common_order'])){
	$send_id = $_GET['break_common_order'];
	$sql = "UPDATE amazon_response_list SET send_id = concat('amz',id) WHERE send_id = '{$send_id}'";
	$res = $db->execute($sql);

	//日志
	$do = '[拆单]：'.$send_id;
	oms_log($u_name,$do,'amazon_order');
	echo 'ok';
}



//导入亚马逊订单 = 手动
if(isset($_GET['import_add_list'])){
	$file = $dir."/../../uploads/amazon_add_list.txt";
    $lines = file_get_contents($file); 
    $lines = mb_convert_encoding($lines, 'utf-8', 'Shift-JIS');	//编码转换
    ini_set('memory_limit', '-1');	//不要限制Mem大小，否则会报错 
    $arr=explode("\n",$lines);

    //获取店铺
    $store = addslashes($_GET['store']);

    //日志
	$do = '[START] 导入订单：'.$store;
	oms_log($u_name,$do,'amazon_import');
    
    //所有oms_has状态变成0
 	$sql = "UPDATE amazon_response_list SET oms_has_me = '0'";
    $res = $db->execute($sql);

    //清空导入表
    $sql = "TRUNCATE amazon_import_list";
    $res = $db->execute($sql);

    //初始化数
    $count_order = 0;
	$insert_count = 0;
	$has_count = 0;
    array_shift($arr);	//删除标题
    foreach ($arr as $key => $value) {
    	$new_arr = explode("	",$value);	//分割字段

    	if(count($new_arr) == '36'){	//如果为36个字段，则数据正确
    		//debug
    		// print_r($new_arr);echo '<hr>'; 
    		// echo $new_arr[21];
    		//计算总数
    		$count_order = $count_order + 1;

			//获取订单信息
			$amazon_order_id = $new_arr[0];	#订单号
			$order_item_id = $new_arr[1];	#子订单号
			$purchase_date = $new_arr[2];	#购买时间
			$buyer_email = $new_arr[4];		#购买人邮箱
			$buyer_name = $new_arr[5];	#购买人
			#$buyer_phone = $new_arr[6];	#不全，删
			$sku = $new_arr[7];	#sku
			$goods_title = $new_arr[8];	#商品名
			$goods_num = $new_arr[9];	#购买数量
			$currency = $new_arr[10];	#货币种类
			$item_price = $new_arr[11];	#金额
			$item_tax = $new_arr[12];	#税
			$shipping_price = $new_arr[13];	#运费
			$shipping_tax = $new_arr[14];	#运费税
			if($new_arr[15]=='Standard'){
				$order_type = 'StandardOrder';	#默认stand 运费种类
			}else{
				$order_type = 'Other';
			}
			$receive_name = $new_arr[16];	#收货人
			$address = $new_arr[20].$new_arr[21].$new_arr[17].$new_arr[18].$new_arr[19];	#配送地址
			$post_code = $new_arr[22];	#邮编
			$country = $new_arr[23];	#国家
			$receive_phone = $new_arr[24];	#配送电话
			if(@$receive_phone == ''){

			}else{
				//格式化电话号码
				$receive_phone = str_replace("-","",$receive_phone);	//替换-
				$receive_phone = str_replace(" ","",$receive_phone);	//替换空格
				//计算电话长度
				$receive_phone_len = strlen($receive_phone);
				if($receive_phone_len == 11 or $receive_phone_len == 10){
					$re3 = substr($receive_phone,-4,4);//从后往前截取4位
					$re2 = substr($receive_phone,-8,4);//从后往前截取4位
					$re1 = str_replace($re2.$re3, '', $receive_phone);//截取前几位
					$receive_phone = $re1.'-'.$re2.'-'.$re3;
				}else{
					$receive_phone = '';
				}
			}
			$item_promotion_discount = $new_arr[25];	#可能对应Gift
			$ship_promotion_discount = $new_arr[27];	#对应Shipping_discount
			$payment_method = $new_arr[30];	#代引
			$pay_money = $new_arr[31];	#代引金额
			$cod_money = $new_arr[33];	#cod费

			// 查询是否存在此订单
			$sql = "SELECT * from amazon_response_list WHERE amazon_order_id = '{$amazon_order_id}'";
			$res = $db->getOne($sql);

		    if(empty($res)){
		    	//记录到导入订单列表中
		    	$sql = "INSERT INTO amazon_import_list (
		    		store,
		    		amazon_order_id,
					order_item_id,
					purchase_date,
					buyer_email,
					buyer_name,
					sku,
					goods_title,
					goods_num,
					currency,
					item_price,
					item_tax,
					shipping_price,
					shipping_tax,
					order_type,
					receive_name,
					address,
					post_code,
					country,
					receive_phone,
					item_promotion_discount,
					ship_promotion_discount,
					payment_method,
					pay_money,
					cod_money
				) VALUES (
					'{$store}',
					'{$amazon_order_id}',
					'{$order_item_id}',
					'{$purchase_date}',
					'{$buyer_email}',
					'{$buyer_name}',
					'{$sku}',
					'{$goods_title}',
					'{$goods_num}',
					'{$currency}',
					'{$item_price}',
					'{$item_tax}',
					'{$shipping_price}',
					'{$shipping_tax}',
					'{$order_type}',
					'{$receive_name}',
					'{$address}',
					'{$post_code}',
					'{$country}',
					'{$receive_phone}',
					'{$item_promotion_discount}',
					'{$ship_promotion_discount}',
					'{$payment_method}',
					'{$pay_money}',
					'{$cod_money}'
				)";
				$res = $db->execute($sql);
		    	$insert_count = $insert_count + 1;

		    	//日志
				$do = '[ING] 导入订单：'.$amazon_order_id.' | 收件人：'.$receive_name.' | 商品：'.$sku.'*'.$goods_num;
				oms_log($u_name,$do,'amazon_import');

		    }else{
		    	$sql = "UPDATE amazon_response_list SET oms_has_me = 'has' WHERE amazon_order_id = '{$amazon_order_id}'";
		    	$res = $db->execute($sql);
		    	$has_count = $has_count + 1;
		    	usleep(50000);
		    	continue;
		    }
		}

    }
    //计算单品总金额
    $sql = "UPDATE amazon_import_list SET item_total_money = item_price * goods_num + item_tax + shipping_price + shipping_tax - item_promotion_discount - ship_promotion_discount";
    $res = $db->execute($sql);
    usleep(50000);
    //清空缓存
    $sql = "TRUNCATE amazon_total_price";
    $res = $db->execute($sql);
    usleep(50000);
    //缓存订单总金额和总代引金额
    $sql = "INSERT INTO amazon_total_price (amazon_order_id,order_total_money,pay_money) SELECT amazon_order_id,sum(item_total_money),sum(pay_money) FROM amazon_import_list GROUP BY amazon_order_id";
    $res = $db->execute($sql);
    usleep(50000);
    //更新订单总金额
    $sql = "UPDATE amazon_import_list pp,amazon_total_price tt SET pp.order_total_money = tt.order_total_money WHERE pp.amazon_order_id = tt.amazon_order_id";
    $res = $db->execute($sql);
    usleep(50000);

    //获取当前日期
    $today = date('y-m-d',time());
    //插入主response_list订单表
    $sql = "INSERT INTO amazon_response_list (
    	store,
    	syn_day,
    	order_type,
    	purchase_date,
    	amazon_order_id,
    	order_line,
    	buyer_name,
    	buyer_email,
    	country,
    	order_total_currency,
    	order_total_money,
    	payment_method,
    	pay_money,
    	phone,
    	post_code,
    	address,
    	receive_name,
    	oms_order_info_status,
    	send_id
    ) SELECT 
    	store,
		'{$today}',
		order_type,
		purchase_date,
		amazon_order_id,
		'1',
		buyer_name,
		buyer_email,
		country,
		currency,
		order_total_money,
		payment_method,
		pay_money,
		receive_phone,
		post_code,
		address,
		receive_name,
		'ok',
		amazon_order_id
	FROM amazon_import_list GROUP BY amazon_order_id";
	$res = $db->execute($sql);

	//插入详情
	$sql = "INSERT INTO amazon_response_info (
		store,
		amazon_order_id,
		order_item_id,
		goods_title,
		sku,
		goods_num,
		shipping_price,
		shipping_tax,
		item_price,
		item_tax,
		promotion_discount,
		shipping_discount,
		cod_money
	) SELECT
		store,
		amazon_order_id,
		order_item_id,
		goods_title,
		sku,
		goods_num,
		shipping_price,
		shipping_tax,
		item_total_money,-- 这里是计算后所得结果
		item_tax,
		item_promotion_discount,
		ship_promotion_discount,
		cod_money
	FROM amazon_import_list";
	$res = $db->execute($sql);

    //final_res
	$final_res['status'] = 'ok';
	$final_res['count_order'] = $count_order;
	$final_res['insert_count'] = $insert_count;
	$final_res['has_count'] = $has_count;

	//日志
	$do = '[END] 导入订单：'.$store.'总单数：'.$count_order.' | 导入数：'.$insert_count.' | 已存在：'.$has_count;
	oms_log($u_name,$do,'amazon_import');

	echo json_encode($final_res);
}
