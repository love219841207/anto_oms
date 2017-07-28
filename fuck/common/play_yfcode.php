<?php
require_once("../header.php");

function play_yf_code($station,$response_list,$response_info,$order_id){
	$db = new PdoMySQL();
	// 检测是否为COD订单，客人指定配送方式
	$sql = "SELECT payment_method,buyer_send_method FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	if($res['payment_method'] == 'COD'){
		$is_cod = 1;
	}else{
		$is_cod = 0;
	}
	$buyer_send_method = $res['buyer_send_method'];

	// 提取该订单的所有子订单的运费代码
	$has_all_yf = 1; //默认全部存在且全部开启
	$yf_strs = '';	//初始化该单运费代码集
	$sql = "SELECT id,yfcode FROM $response_info WHERE order_id = '{$order_id}'";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$now_info_id = $value['id'];
		$now_yf_code = $value['yfcode'];
		$yf_strs = $yf_strs.',\''.$now_yf_code.'\'';

		// 检测运费代码是否存在
		$sql = "SELECT count(1) as count FROM yf_code WHERE yf_code_name = 'z' AND status = 1";
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
		$sql = "SELECT need_cod,yf_code_name,send_method FROM yf_code WHERE level = '{$max_level}'";
		$res = $db->getOne($sql);
		$need_cod = $res['need_cod'];
		$max_code = $res['yf_code_name'];
		$send_method = $res['send_method'];
		
		// 判断COD
		if($is_cod == 1){
			//如果是COD订单查看是否支持此运费代码
			if($need_cod == 0){	//如果不支持COD订单，此订单中最大的运费代码，报错
				$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE order_id = '{$order_id}' AND yfcode = '{$max_code}'";
				$res = $db->execute($sql);
			}
		}

		// 客人指定配送方式判断
		if($buyer_send_method <> $send_method){	// 如果不一致，报错
			$sql = "UPDATE $response_info SET yfcode_ok = 2 WHERE order_id = '{$order_id}' AND yfcode = '{$max_code}'";
			$res = $db->execute($sql);
		}
	}
}
