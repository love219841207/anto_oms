<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入乐天订单 = 手动
if(isset($_GET['import_add_list'])){
	$filename = $dir."/../uploads/rakuten_add_list.csv";

	function fgetcsv_reg(& $handle, $length = null, $d = ',', $e = '"') {
	$d = preg_quote($d);
	$e = preg_quote($e);
	$_line = "";
	$eof=false;
	while ($eof != true) {
	$_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
	$itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
	if ($itemcnt % 2 == 0)
	$eof = true;
	}
	$_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
	$_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
	preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
	$_csv_data = $_csv_matches[1];
	for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
	$_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
	$_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
	}
	return empty ($_line) ? false : $_csv_data;
	}

	//获取店铺
    $store = addslashes($_GET['store']);

    //日志
	$do = '[START] 导入订单：'.$store;
	oms_log($u_name,$do,'rakuten_import','rakuten',$store,'-');

	//清空导入表
    $sql = "TRUNCATE rakuten_import_list";
    $res = $db->execute($sql);
    // $sql = "TRUNCATE rakuten_response_list";
    // $res = $db->execute($sql);
    // $sql = "TRUNCATE rakuten_response_info";
    // $res = $db->execute($sql);

    //所有oms_has状态变成0
 	$sql = "UPDATE rakuten_response_list SET oms_has_me = '0'";
    $res = $db->execute($sql);

    //初始化数
    $count_order = 0;
	$insert_count = 0;
	$has_count = 0;

	$file = fopen($filename,'r'); 
	while ($data = fgetcsv_reg($file)) {
		$goods_list[] = $data;
	}
	array_shift($goods_list);
	foreach ($goods_list as $arr){
		// var_dump($arr);
		// echo count($arr);echo ' # ';
		if(is_array($arr) && !empty($arr)){
			$str = '';
			for($i=0; $i<126; $i++){
				// echo $arr[$i]."<br>";
				$str .= $arr[$i]."|*|";
			}
			$str=mb_convert_encoding($str,"UTF-8","shift-jis");
			$strs = explode("|*|",$str);
			$order_id = $strs[0];

			// 查询是否存在此订单
			$sql = "SELECT * from rakuten_response_list WHERE order_id = '{$order_id}'";
			$res = $db->getOne($sql);

			//计算总数
    		$count_order = $count_order + 1;

			if(empty($res)){
				$order_payment_method = $strs[2];
				$want_date = $strs[6];
				$buyer_others = $strs[14];
				$purchase_date = $strs[15];
				$item_total_money = $strs[19];
				$item_tax = $strs[20];
				$shipping_price = $strs[21];
				$cod_money = $strs[22];
				$pay_money = $strs[23];
				$order_total_money = $strs[24];
				$points = $strs[68];
				$goods_title = $strs[101].'@'.$strs[102];
				$sku = $strs[102];
				$goods_num = $strs[105];
				$unit_price = $strs[104];
				$payment_method = $strs[59];
				$coupon = $strs[118];
				$buyer_post_code = $strs[44].'-'.$strs[45];
				$buyer_address = addslashes($strs[46].$strs[47].$strs[48]);
				$buyer_name = $strs[49].$strs[50];
				$buyer_email = $strs[56];
				$buyer_phone = $strs[53].$strs[54].$strs[55];
				$post_code = $strs[88].'-'.$strs[89];
				$address = addslashes($strs[90].$strs[91].$strs[92]);
				$receive_name = $strs[93].$strs[94];
				$receive_phone = $strs[97].$strs[98].$strs[99];
				$buyer_send_method = $strs[66];

				// 客人备注（配送)
				$buyer_others = str_replace('[配送日時指定:]','',$buyer_others);
				$buyer_others = str_replace('~','～',$buyer_others);
				$buyer_others = str_replace('〜','～',$buyer_others);
				$buyer_others = trim($buyer_others);
				$time_index = strpos($buyer_others, '～');
				$want_time = substr($buyer_others, $time_index,$time_index+4);
				$want_time = trim($want_time);
				if($want_time == '～12:00'){
					$want_time = '09:00～12:00';
				}else if($want_time == '～14:00'){
					$want_time = '12:00～14:00';
				}else if($want_time == '～16:00'){
					$want_time = '14:00～16:00';
				}else if($want_time == '～18:00'){
					$want_time = '16:00～18:00';
				}else if($want_time == '～20:00'){
					$want_time = '18:00～20:00';
				}else if($want_time == '～21:00'){
					$want_time = '20:00～21:00';
				}else{
					$want_time = '';
				}

				// 代金引換转COD
				$payment_method = str_replace('代金引換','COD',$payment_method);

				// 修改手机格式
				$by3 = substr ($buyer_phone,-4,4);
				$by2 = substr ($buyer_phone,-8,4);
				$by1 = str_replace ($by2.$by3, '',$buyer_phone);
				$buyer_phone = $by1.'-'.$by2.'-'.$by3;

				$re3 = substr($receive_phone,-4,4);//从后往前截取4位
				$re2 = substr($receive_phone,-8,4);//从后往前截取4位
				$re1 = str_replace($re2.$re3, '', $receive_phone);//截取前几位
				$receive_phone = $re1.'-'.$re2.'-'.$re3;

				// 修改 佐川急便メール便（規定：厚さ2ｃｍ以内）の商品は「代引き」できません。 为 メール便
				if($buyer_send_method == '佐川急便メール便（規定：厚さ2ｃｍ以内）の商品は「代引き」できません。' or $buyer_send_method == 'ネコポス便'){
					$buyer_send_method = 'メール便';
				}

				// 提取运费代码
				$yfcode = substr($sku,0,1);

				// 拆分赠品开始 - - - - - - - - - - - - - - - - 
				// $sku_copy = str_replace($yfcode.'-', '', $sku);
				$sku_copy = preg_replace('/'.$yfcode.'-'.'/', '', $sku, 1); 
				$res_sku = explode('_', $sku_copy);	

				// 主商品代码	
				$count_goods_code = count($res_sku);
				$goods_code_main = trim($res_sku[$count_goods_code-1]);

				// if($strs[1]==='新規受付'){	//只导入新规订单
				$sql = "INSERT INTO rakuten_import_list(
					store,			#店铺名
					order_id,			#订单号
					order_payment_method,	#信用卡结算状态
					buyer_others,	#客人备注（配送）
					purchase_date,	#订单日期
					item_total_money,	#合计
					item_tax,	#消费税
					shipping_price,	#运费
					cod_money,	#代引手续费
					pay_money,	#代引金额
					order_total_money,	#合计金额
					points,	#积分
					goods_title,	#商品名
					sku,	#sku
					goods_num,	#购买数
					unit_price,	#单价
					buyer_post_code,	#购买者邮编	
					buyer_address,	#购买者地址
					buyer_name,	#购买人
					buyer_phone,	#购买人手机
					buyer_email,	#购买人邮箱
					post_code,	#收件邮编
					address,	#收件地址
					receive_name,	#收件人姓名
					receive_phone,	#收件人电话
					payment_method,	#支付方式
					buyer_send_method,	#客人要求配送方式
					coupon,
					yfcode,
					want_date,
					want_time
				)VALUES(
					'{$store}',	#店铺名
					'{$order_id}',	#订单号
					'{$order_payment_method}',	#信用卡结算状态	
					'{$buyer_others}',	#客人备注（配送）
					'{$purchase_date}',	#订单日期
					'{$item_total_money}',	#合计
					'{$item_tax}',	#消费税
					'{$shipping_price}',	#运费
					'{$cod_money}',	#代引手续费
					'{$pay_money}',	#代引金额
					'{$order_total_money}',	#合计金额
					'{$points}',	#积分
					'{$goods_title}',	#商品名
					'{$goods_code_main}',	#sku
					'{$goods_num}',	#购买数
					'{$unit_price}',	#单价
					'{$buyer_post_code}',	#购买人邮编
					'{$buyer_address}',	#购买者地址
					'{$buyer_name}',	#购买人
					'{$buyer_phone}',	#购买人手机
					'{$buyer_email}',	#购买人邮箱
					'{$post_code}', #收件邮编
					'{$address}',	#收件地址
					'{$receive_name}',	#收件姓名
					'{$receive_phone}',	#收件人电话
					'{$payment_method}',	#支付方式
					'{$buyer_send_method}',	#客人要求配送方式
					'{$coupon}',	#优惠券
					'{$yfcode}',
					'{$want_date}',
					'{$want_time}'
				)";
				$res = $db->execute($sql);

				// 赠品
				
				if($count_goods_code == 1){

				}else{
					for($i=0; $i<$count_goods_code-1; $i++){
						$now_goods_code = trim($res_sku[$i]);
						// if($strs[1]==='新規受付'){	//只导入新规订单
						$sql = "INSERT INTO rakuten_import_list(
							store,			#店铺名
							order_id,			#订单号
							order_payment_method,	#信用卡结算状态
							buyer_others,	#客人备注（配送）
							purchase_date,	#订单日期
							item_total_money,	#合计
							item_tax,	#消费税
							shipping_price,	#运费
							cod_money,	#代引手续费
							pay_money,	#代引金额
							order_total_money,	#合计金额
							points,	#积分
							goods_title,	#商品名
							sku,	#sku
							goods_num,	#购买数
							unit_price,	#单价
							buyer_post_code,	#购买者邮编	
							buyer_address,	#购买者地址
							buyer_name,	#购买人
							buyer_phone,	#购买人手机
							buyer_email,	#购买人邮箱
							post_code,	#收件邮编
							address,	#收件地址
							receive_name,	#收件人姓名
							receive_phone,	#收件人电话
							payment_method,	#支付方式
							buyer_send_method,	#客人要求配送方式
							coupon,
							yfcode,
							want_date,
							want_time
						)VALUES(
							'{$store}',	#店铺名
							'{$order_id}',	#订单号
							'{$order_payment_method}',	#信用卡结算状态	
							'{$buyer_others}',	#客人备注（配送）
							'{$purchase_date}',	#订单日期
							'0',	#合计
							'0',	#消费税
							'0',	#运费
							'0',	#代引手续费
							'0',	#代引金额
							'0',	#合计金额
							'0',	#积分
							'{$goods_title}',	#商品名
							'{$now_goods_code}',	#sku
							'{$goods_num}',	#购买数
							'0',	#单价
							'{$buyer_post_code}',	#购买人邮编
							'{$buyer_address}',	#购买者地址
							'{$buyer_name}',	#购买人
							'{$buyer_phone}',	#购买人手机
							'{$buyer_email}',	#购买人邮箱
							'{$post_code}', #收件邮编
							'{$address}',	#收件地址
							'{$receive_name}',	#收件姓名
							'{$receive_phone}',	#收件人电话
							'{$payment_method}',	#支付方式
							'{$buyer_send_method}',	#客人要求配送方式
							'{$coupon}',	#优惠券
							'{$yfcode}',
							'{$want_date}',
							'{$want_time}'
						)";
						$res = $db->execute($sql);
					// }
					}
				}
				
				// 拆分赠品结束 - - - - - - - - - - - - - - - - 

		    	$insert_count = $insert_count + 1;

		    	//日志
				$do = '[ING] 导入订单：'.$order_id.' | 收件人：'.$receive_name.' | 商品：'.$sku.'*'.$goods_num;
				oms_log($u_name,$do,'rakuten_import','rakuten',$store,'-');
				// }

			}else{
				$sql = "UPDATE rakuten_response_list SET oms_has_me = 'has' WHERE order_id = '{$order_id}'";
		    	$res = $db->execute($sql);
		    	$has_count = $has_count + 1;
		    	usleep(50000);
		    	continue;
			}
		}
	} 

 	fclose($file);  

    //获取当前日期
    $today = date('y-m-d',time());
    //插入主response_list订单表
    $sql = "INSERT INTO rakuten_response_list (
    	store,
    	syn_day,	#同步日期（导入日期）
		order_id,	#订单号
		order_line,	#order_line
		order_payment_method,	#信用卡结算状态
		buyer_others,	#购买者配送备注
		buyer_send_method,	#购买者要求配送方式
		purchase_date,	#订单付款日期
		buyer_name,	#购买人
		buyer_email,	#邮箱
		buyer_phone,	#购买人手机
		buyer_address,	#购买人地址
		buyer_post_code,	#购买人邮编
		order_total_money,	#订单总金额
		coupon,	#优惠券
		points,	#积分
		shipping_price,	#运费
		payment_method,	#付款方式
		pay_money,	#代引金额
		phone,	#配送手机
		post_code,	#配送邮编
		address,	#配送地址
		receive_name,	#收件人
		oms_order_info_status,
		send_id,
		want_date,
		want_time
    ) SELECT 
    	store,
		'{$today}',
		order_id,
		CASE WHEN payment_method = 'COD' THEN '1' ELSE '-2' END,
		order_payment_method,
		buyer_others,
		buyer_send_method,
		purchase_date,
		buyer_name,
		buyer_email,	#邮箱
		buyer_phone,	#购买人手机
		buyer_address,	#购买人地址
		buyer_post_code,	#购买人邮编
		order_total_money,	#订单总金额
		coupon,	#优惠券
		points,	#积分
		shipping_price,	#运费
		payment_method,	#付款方式
		pay_money,	#代引金额
		receive_phone,	#配送手机
		post_code,	#配送邮编
		address,	#配送地址
		receive_name,	#收件人
		'ok',
		'ready',
		want_date,
		want_time
	FROM rakuten_import_list GROUP BY order_id";
	$res = $db->execute($sql);

	//更新send_id
	$sql = "UPDATE rakuten_response_list SET send_id = concat('rku',id) WHERE send_id = 'ready'";
	$res = $db->execute($sql);

	// 当前时间戳
	$now_time = time();

	//插入详情
	$sql = "INSERT INTO rakuten_response_info (
		store,
		order_id,
		goods_title,
		sku,
		goods_code,
		goods_num,
		item_price,
		unit_price,
		item_tax,
		cod_money,
		import_time,
		yfcode
	) SELECT
		store,
		order_id,
		goods_title,
		sku,
		goods_code,
		goods_num,
		goods_num*unit_price,
		unit_price,
		item_tax,
		cod_money,
		{$now_time},
		yfcode
	FROM rakuten_import_list";
	$res = $db->execute($sql);

    //final_res
	$final_res['status'] = 'ok';
	$final_res['count_order'] = $count_order;
	$final_res['insert_count'] = $insert_count;
	$final_res['has_count'] = $has_count;

	//日志
	$do = '[END] 导入订单：'.$store.' 总单数：'.$count_order.' | 导入数：'.$insert_count.' | 已存在：'.$has_count;
	oms_log($u_name,$do,'rakuten_import','rakuten',$store,'-');

	echo json_encode($final_res);
}