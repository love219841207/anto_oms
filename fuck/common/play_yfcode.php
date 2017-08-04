<?php
require_once("../header.php");

function play_yf_code($station,$response_list,$response_info,$send_id){
	$db = new PdoMySQL();
	// 检测是否为COD订单，客人指定配送方式
	$sql = "SELECT payment_method,buyer_send_method,address FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);
	if($res['payment_method'] == 'COD'){
		$is_cod = 1;
	}else{
		$is_cod = 0;
	}
	$buyer_send_method = $res['buyer_send_method'];
	$address = $res['address'];

	$sql = "SELECT order_id FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getAll($sql);

	$order_ids = '';
	foreach ($res as $value) {
		$order_ids = $order_ids.',\''.$value['order_id'].'\'';
	}
	$order_ids = substr($order_ids, 1);	//移除逗号
	$order_ids = trim($order_ids);	//空白
		
	// 提取该订单的所有子订单的运费代码
	$has_all_yf = 1; //默认全部存在且全部开启
	$yf_strs = '';	//初始化该单运费代码集
	$sql = "SELECT id,yfcode FROM $response_info WHERE order_id in ({$order_ids})";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$now_info_id = $value['id'];
		$now_yf_code = $value['yfcode'];
		$yf_strs = $yf_strs.',\''.$now_yf_code.'\'';

		// 检测运费代码是否存在
		$sql = "SELECT count(1) as count FROM yf_code WHERE yf_code_name = '{$now_yf_code}' AND status = 1";
		$res = $db->getOne($sql);
		if($res['count'] == 0){
			//如果不存在或者状态关闭，报错
			$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE id = '{$now_info_id}'";
			$res = $db->execute($sql);
			$has_all_yf = 0;
		}
	}

	$yf_strs = substr($yf_strs, 1);	//移除逗号
	$yf_strs = trim($yf_strs);	//空白

	// 优先级检测
	if($has_all_yf == 1){	// 运费代码都存在且开启
		$sql = "SELECT MAX(level) as max_level FROM yf_code WHERE yf_code_name in ($yf_strs)";
		$res = $db->getOne($sql);
		$max_level = $res['max_level'];
		$sql = "SELECT need_cod,yf_code_name,send_method,default_yf,default_one_yf FROM yf_code WHERE level = '{$max_level}'";
		$res = $db->getOne($sql);
		$need_cod = $res['need_cod'];
		$max_code = $res['yf_code_name'];
		$send_method = $res['send_method'];
		$default_yf = $res['default_yf'];
		$default_one_yf = $res['default_one_yf'];
		
		// 判断COD
		if($is_cod == 1){
			//如果是COD订单查看是否支持此运费代码
			if($need_cod == 0){	//如果不支持COD订单，此订单中最大的运费代码，报错
				$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE order_id in ({$order_ids}) AND yfcode = '{$max_code}'";
				$res = $db->execute($sql);
			}
		}

		// 客人指定配送方式判断
		if($buyer_send_method !== $send_method){	// 如果不一致，报错
			$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE order_id in ({$order_ids}) AND yfcode = '{$max_code}'";
			$res = $db->execute($sql);
		}else{
			$sql = "UPDATE $response_info SET yfcode_ok = 1 WHERE order_id in ({$order_ids}) AND yfcode = '{$max_code}'";
			$res = $db->execute($sql);
		}

		// 运费计算
			// 取前三字去模糊匹配
		$address = mb_substr($address,0,3);	
		$sql = "SELECT count(1) as count,yf_money,yf_add FROM yf_money WHERE yf_code = '{$max_code}' AND area like '%{$address}%'";
		$res = $db->getOne($sql);
		if($res['count'] == 1){
			// 如果是特殊地区，取出特殊运费和特殊叠加运费
			$final_yf_money = $res['yf_money'];
			// 更新叠加运费
			$sql = "UPDATE $response_info info,yf_money SET info.yf_add = yf_money.yf_add WHERE info.yfcode = yf_money.yf_code AND area like '%{$address}%' AND order_id in ({$order_ids})";
			$res = $db->execute($sql);

		}else{
			// 正常运费
			$final_yf_money = $default_yf;
			// 更新叠加运费
			$sql = "UPDATE $response_info info,yf_code SET info.yf_add = yf_code.default_one_yf WHERE info.yfcode = yf_code.yf_code_name AND order_id in ({$order_ids})";
			$res = $db->execute($sql);
		}

		// 求叠加运费
		$sql = "SELECT sum(yf_add * goods_num) AS all_add FROM $response_info WHERE order_id in ({$order_ids})";
		$res = $db->getOne($sql);
		$all_add = $res['all_add'];

		// 多加的追加运费
		$sql = "SELECT yf_add FROM $response_info WHERE yfcode = '{$max_code}' AND order_id in ({$order_ids})";
		$res = $db->getOne($sql);

		// 最终计算运费
		$final_yf_money = $final_yf_money + $all_add - $res['yf_add'];
		$sql = "UPDATE $response_list SET all_yfmoney = '{$final_yf_money}' WHERE order_id in ({$order_ids})";
		$res = $db->execute($sql);

		// 最终运费与获取的订单运费对比，不一致报错
		$sql = "SELECT all_yfmoney - shipping_price as yf_pass FROM $response_list WHERE order_id in ({$order_ids})";
		$res = $db->getOne($sql);
		$yf_pass = $res['yf_pass'];

		if($yf_pass == 0){
			$sql = "UPDATE $response_list SET yfcode_ok = 1 WHERE order_id in ({$order_ids})";
			$res = $db->execute($sql);
			$sql = "UPDATE $response_info SET yfcode_ok = 1 WHERE order_id in ({$order_ids})";
			$res = $db->execute($sql);
		}else{
			$sql = "UPDATE $response_list SET yfcode_ok = 2 WHERE order_id in ({$order_ids})";
			$res = $db->execute($sql);
			$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE order_id in ({$order_ids})";
			$res = $db->execute($sql);
		}	

	}


}
