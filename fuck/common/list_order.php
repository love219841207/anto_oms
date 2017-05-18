<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 获取用户分页数
if(isset($_GET['get_pagesize'])){
	$get_pagesize = $_GET['get_pagesize'];
	$sql = "SELECT page_size FROM user_oms WHERE u_num = '{$_SESSION['oms_u_num']}'";
	$res = $db->getOne($sql);
	echo $res['page_size'];
}

// 查询已存在订单号
if(isset($_GET['has_orders'])){
	$store = $_GET['has_orders'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';

	$sql = "SELECT order_id FROM $response_list WHERE oms_has_me = 'has' ORDER BY id DESC";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//一键合单
if(isset($_GET['onekey_common_order'])){
	$store = $_GET['onekey_common_order'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$sql = "
		UPDATE $response_list a,
		(SELECT a.id FROM $response_list a,
		(SELECT receive_name,count(id) as num
		FROM $response_list WHERE store='{$store}' AND post_ok = '1' AND tel_ok = '1' AND sku_ok = '1' AND yfcode_ok='1' AND order_line = '1'
		group by receive_name,phone,post_code,buyer_email,address
		having num>1) b
		WHERE a.receive_name = b.receive_name) b
		SET a.send_id = concat('H',a.phone)
		WHERE a.id = b.id";
	$res = $db->execute($sql);

	//修正合单号
	$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND order_line = '1' AND send_id LIKE 'H%' GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$now_send_id = $value['send_id'];
		$sql = "SELECT id FROM $response_list WHERE send_id = '{$now_send_id}' LIMIT 1";
		$res = $db->getOne($sql);
		$id = $res['id'];
		$sql = "UPDATE $response_list SET send_id = concat('Hamz',$id) WHERE send_id = '{$now_send_id}'";
		$res = $db->execute($sql);
	}

	//order_line
	$sql = "UPDATE $response_list SET order_line = '2' WHERE order_line = '1' AND store = '{$store}' AND post_ok = '1' AND tel_ok = '1' AND sku_ok = '1' AND yfcode_ok='1'";
	$res = $db->execute($sql);

	//查询所有合单号
	$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND order_line = '2' AND send_id LIKE 'H%' GROUP BY send_id";
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
	$play = $station.'_order';
	oms_log($u_name,$do,$play,$station,$store,'-');

	echo json_encode($res);
}

//查询合单列表
if(isset($_GET['list_common_order'])){
	$store = $_GET['list_common_order'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';

	//查询所有合单号
	$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND send_id LIKE 'H%' AND order_line = '2' GROUP BY send_id";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//查询单个合单详情
if(isset($_GET['get_common_order'])){
	$send_id = $_GET['get_common_order'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';

	$sql = "SELECT * FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//拆单
if(isset($_GET['break_common_order'])){
	$send_id = $_GET['break_common_order'];
	$store = $_GET['store'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';

	$sql = "UPDATE $response_list SET send_id = concat('amz',id) WHERE send_id = '{$send_id}'";
	$res = $db->execute($sql);

	//日志
	$do = '[拆单]：'.$send_id;
	$play = $station.'_order';
	oms_log($u_name,$do,$play,$station,$store,'-');
	echo 'ok';
}

//扣库存
if(isset($_GET['sub_repo'])){
	$today = date('y-m-d',time()); //获取日期
	
	$now_station = strtolower($_GET['station']);

	if($now_station == 'all_station'){
		// 如果是所有平台扣库存，即冻结表
		$sql = "SELECT id,station,send_id FROM amazon_response_list WHERE order_line = 3 UNION SELECT id,station,send_id FROM yahoo_response_list WHERE order_line = 3 GROUP BY send_id ORDER BY id";

	}else{
		// 单个平台正常店铺发货
		$store = $_GET['store'];
		$now_response_list = $now_station.'_response_list';

		$sql = "SELECT station,send_id FROM $now_response_list WHERE order_line = 2 AND store = '{$store}' GROUP BY send_id";
	}

	// 按照 send_id 扣库存	
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$station = $val['station'];
		$response_list = $val['station'].'_response_list';
		$response_info = $val['station'].'_response_info';
		$send_id = $val['send_id'];

		// 查询出每个 send_id 对应的 order_id 的 商品代码
		$sql = "SELECT info.order_id as order_id,info.id as info_id,info.goods_code as goods_code,info.pause_ch as pause_ch,info.pause_jp as pause_jp,info.goods_num as goods_num FROM $response_info info,$response_list list WHERE list.order_id = info.order_id AND list.send_id = '{$send_id}'";
		$res2 = $db->getAll($sql);

		// 默认可发货
		$can_send = 1;
		$order_ids = '';
		//遍历出订单
		foreach ($res2 as $val2) {
			$now_order_id = $val2['order_id'];
			$now_goods_code = $val2['goods_code'];
			$u_goods_num = $val2['goods_num'];
			$pause_ch = $val2['pause_ch'];
			$pause_jp = $val2['pause_jp'];

			$goods_num = $u_goods_num - $pause_ch - $pause_jp;	//实际需要库存数 = 购买数 - 押中国 - 押日本
			$info_id = $val2['info_id'];

			$order_ids = '\''.$now_order_id.'\','.$order_ids;	# 拼接总订单号集合

			// 查询日本库存
			$sql = "SELECT b_repo FROM goods_type WHERE goods_code = '{$now_goods_code}'";
			$res = $rdb->getOne($sql);
			$b_repo = $res['b_repo'];

			// 数量大于日本
			if($goods_num > $b_repo){
				// 消耗掉日本库存
				$sql = "UPDATE goods_type SET b_repo = 0 WHERE goods_code = '{$now_goods_code}'";
				$res = $rdb->execute($sql);

				// 押日本
				$sql = "UPDATE $response_info SET pause_jp = pause_jp + $b_repo WHERE id = '{$info_id}'";
				$res = $db->execute($sql);

				// 查询中国库存
				$sql = "SELECT a_repo FROM goods_type WHERE goods_code = '{$now_goods_code}'";
				$res = $rdb->getOne($sql);
				$a_repo = $res['a_repo'];

				$need_num = $goods_num - $b_repo;
				// 数量大于中国
				if($need_num > $a_repo){
					// 消耗掉中国库存
					$sql = "UPDATE goods_type SET a_repo = 0 WHERE goods_code = '{$now_goods_code}'";
					$res = $rdb->execute($sql);

					// 押中国
					$sql = "UPDATE $response_info SET pause_ch = pause_ch + $a_repo WHERE id = '{$info_id}'";
					$res = $db->execute($sql);
				}else{
					// 数量小于等于中国
					$sql = "UPDATE goods_type SET a_repo = a_repo - $need_num WHERE goods_code = '{$now_goods_code}'";
					$res = $rdb->execute($sql);

					// 押中国
					$sql = "UPDATE $response_info SET pause_ch = pause_ch + $need_num WHERE id = '{$info_id}'";
					$res = $db->execute($sql);
				}	
			}else{
				// 数量小于等于日本，消耗日本库存
				$sql = "UPDATE goods_type SET b_repo = b_repo - $goods_num WHERE goods_code = '{$now_goods_code}'";
				$res = $rdb->execute($sql);

				// 押日本
				$sql = "UPDATE $response_info SET pause_jp = pause_jp + $goods_num WHERE id = '{$info_id}'";
				$res = $db->execute($sql);
			}

			// 对比数量与（押日本+押中国）
			$sql = "SELECT pause_ch+pause_jp AS pause_num FROM $response_info WHERE id = '{$info_id}'";
			$res = $db->getOne($sql);
			$pause_num = $res['pause_num'];
			if($u_goods_num == $pause_num){
				// 可发货
				$sql = "UPDATE $response_info SET is_pause = 'pass' WHERE id = '{$info_id}'";
				$res = $db->execute($sql);

			}else{
				// 不可发货
				$can_send = 0;
				if($now_station == 'all_station'){

				}else{
					$sql = "UPDATE $response_info SET is_pause = 'pause' WHERE id = '{$info_id}'";
					$res = $db->execute($sql);
				}
			}
		}

		if($can_send == 0){
			// 没有货，冻结订单
			$sql = "UPDATE $response_list SET order_line = '3' WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}else if($can_send == 1){
			// 更新order_line
			$sql = "UPDATE $response_list SET order_line = '4' WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}	

		$order_ids = rtrim($order_ids,",");
		$order_ids = '('.$order_ids.')';
		$sql = "SELECT sum(pause_ch) AS sum_ch,sum(pause_jp) AS sum_jp FROM $response_info WHERE order_id IN $order_ids";
		$res = $db->getOne($sql);
		$sum_ch = $res['sum_ch'];
		$sum_jp = $res['sum_jp'];

		$repo_status = '';
		if($sum_jp > 0){
			if($sum_ch > 0){
				$repo_status = '中+日';	#中+日
			}else if($sum_ch == 0){
				$repo_status = '日';	#日
			}
		}else{
			if($sum_ch > 0){
				$repo_status = '中';	#中
			}else if($sum_ch == 0){
				$repo_status = '缺货';	#无
			}
		}

		$sql = "UPDATE $response_list SET repo_status = '{$repo_status}' WHERE send_id = '{$send_id}'";
		$res = $db->execute($sql);
	}

	// 亚马逊转入发货表
	$sql = "INSERT INTO send_table (
		station,
		order_id,
		send_id,	#合单发货ID
		oms_id,	#OMS-ID
		info_id, #info-ID
		sku, 	#sku，客人看
		goods_code,	#商品代码，仓库看
		out_num,	#商品数量
		pause_jp,	#押日本
		pause_ch,	#押中国
		repo_status,    #出仓方式
		who_tel,	#配送电话
		who_post,	#邮编
		who_house,	#地址
		who_name,	#收货人
		is_cod,		#是否代引
		due_money,	#代引金额，写出全部的item金额，根据cod，更新是否是代引
		send_method,
		who_email,	#邮编
		store_name,	#店铺名
		holder,		#担当者
		import_day) SELECT	#导入日期 
		'{$station}',
		list.order_id,
		list.send_id,
		list.id,
		info.id,
		info.sku,
		info.goods_code,
		info.goods_num,
		info.pause_jp,
		info.pause_ch,
		list.repo_status,
		list.phone,
		list.post_code,
		list.address,
		list.receive_name,
		list.payment_method,
		list.pay_money,	#带引金额
		info.yfcode,
		list.buyer_email,
		list.store,
		'{$u_name}',
		'{$today}' from amazon_response_list list,amazon_response_info info where list.order_id = info.order_id AND list.order_line = '4'";
	$res = $db->execute($sql);

	// 雅虎转入发货表
	// 乐天转入发货表

	// 更新order_line
	$sql = "UPDATE amazon_response_list SET order_line = '5' WHERE order_line = '4'";
	$res = $db->execute($sql);
	// $sql = "UPDATE yahoo_response_list SET order_line = '5' WHERE order_line = '4'";
	// $res = $db->execute($sql);
	// $sql = "UPDATE rakuten_response_list SET order_line = '5' WHERE order_line = '4'";
	// $res = $db->execute($sql);
	echo 'ok';

}