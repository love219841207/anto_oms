<?php
require_once("../header.php");
require_once("../log.php");
require_once("./play_price.php");
require_once("./play_yfcode.php");
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

// 合单收件人地址再核对
if(isset($_GET['cc_com_order'])){
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';

	# 清空错误表
	$sql = "TRUNCATE com_addr_error";
	$res = $db->execute($sql);
	$sql = "TRUNCATE com_name_error";
	$res = $db->execute($sql);
	$sql = "TRUNCATE com_date_error";
	$res = $db->execute($sql);
	$sql = "TRUNCATE com_time_error";
	$res = $db->execute($sql);

	#查询插入错误日期
	$sql = "INSERT INTO com_date_error SELECT id,send_id,want_date from $response_list where order_line in(1,2) and send_id LIKE 'H%' group by want_date";
	$res = $db->execute($sql);

	#删除不是错的
	$sql = "SELECT send_id,count(send_id) as c_num FROM com_date_error GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$send_id = $val['send_id'];
		if($val['c_num'] == 1){
			$sql = "DELETE FROM com_date_error WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}
	}

	#查询插入错误时间
	$sql = "INSERT INTO com_time_error SELECT id,send_id,want_time from $response_list where order_line in(1,2) and send_id LIKE 'H%' group by want_time";
	$res = $db->execute($sql);

	#删除不是错的
	$sql = "SELECT send_id,count(send_id) as c_num FROM com_time_error GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$send_id = $val['send_id'];
		if($val['c_num'] == 1){
			$sql = "DELETE FROM com_time_error WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}
	}

	#查询插入错误地址
	$sql = "INSERT INTO com_addr_error SELECT id,send_id,address from $response_list where order_line in(1,2) and send_id LIKE 'H%' group by address";
	$res = $db->execute($sql);

	#删除不是错的
	$sql = "SELECT send_id,count(send_id) as c_num FROM com_addr_error GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$send_id = $val['send_id'];
		if($val['c_num'] == 1){
			$sql = "DELETE FROM com_addr_error WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}
	}
	
	#查询插入错误人名
	$sql = "INSERT INTO com_name_error SELECT id,send_id,receive_name from $response_list where order_line in(1,2) and send_id LIKE 'H%' group by receive_name";
	$res = $db->execute($sql);

	#删除不是错的人名
	$sql = "SELECT send_id,count(send_id) as c_num FROM com_name_error GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$send_id = $val['send_id'];
		if($val['c_num'] == 1){
			$sql = "DELETE FROM com_name_error WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}
	}

	$sql = "SELECT * FROM com_addr_error";
	$res1 = $db->getAll($sql);
	$sql = "SELECT * FROM com_name_error";
	$res2 = $db->getAll($sql);
	$sql = "SELECT * FROM com_date_error";
	$res3 = $db->getAll($sql);
	$sql = "SELECT * FROM com_time_error";
	$res4 = $db->getAll($sql);
	$final_res['addr'] = $res1;
	$final_res['name'] = $res2;
	$final_res['date'] = $res3;
	$final_res['time'] = $res4;
	echo json_encode($final_res);
}

// 一键合单
if(isset($_GET['onekey_common_order'])){
	$store = $_GET['onekey_common_order'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';
	$response_info = $station.'_response_info';
	if($station == 'amazon'){
		$cct = 'amz';
	}
	if($station == 'rakuten'){
		$cct = 'rku';
	}
	if($station == 'yahoo'){
		$cct = 'yho';
	}

	if($station == 'p_yahoo'){
		// 拍卖店手动合单
		$cct = 'pyho';
	}else{
		//	重置单号
		$sql = "UPDATE $response_list SET all_total_money = order_total_money,send_id = concat('{$cct}',id) WHERE order_line in (1,2)";
		$res = $db->execute($sql);

		$sql = "
			UPDATE $response_list a,
			(SELECT a.id FROM $response_list a,
			(SELECT phone,post_code,count(id) as num
			FROM $response_list WHERE store='{$store}' AND post_ok = '1' AND tel_ok = '1' AND sku_ok = '1' AND yfcode_ok='1' AND order_line in (1,2)
			group by phone,post_code
			having num>1) b
			WHERE a.phone = b.phone and a.post_code = b.post_code and a.order_line in(1,2)) b
			SET a.send_id = concat('H',a.phone)
			WHERE a.id = b.id";
		$res = $db->execute($sql);

		//修正合单号
		$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND order_line in (1,2) AND send_id LIKE 'H%' GROUP BY send_id";
		$res = $db->getAll($sql);
		foreach ($res as $value) {
			$now_send_id = $value['send_id'];
			$sql = "SELECT id FROM $response_list WHERE send_id = '{$now_send_id}' LIMIT 1";
			$res = $db->getOne($sql);
			$id = $res['id'];
			$sql = "UPDATE $response_list SET send_id = concat('H','{$cct}',$id) WHERE send_id = '{$now_send_id}'";
			$res = $db->execute($sql);
		}
	}

	//order_line
	$sql = "UPDATE $response_list SET order_line = '2' WHERE order_line = '1' AND store = '{$store}' AND post_ok = '1' AND tel_ok = '1' AND sku_ok = '1' AND yfcode_ok='1'";
	$res = $db->execute($sql);

	// 暂时更新总合单金额 = 订单金额
	$sql = "UPDATE $response_list SET all_total_money = order_total_money WHERE order_line = '2' AND store = '{$store}'";
	$res = $db->execute($sql);

	// 查询所有合单号
	$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND order_line = '2' AND send_id LIKE 'H%' GROUP BY send_id";
	$res3 = $db->getAll($sql);
	$all_one = '';
	foreach ($res3 as $value) {
		$all_one = $all_one.'['.$value['send_id'].']';
	}

	// 查询所有订单号并计算价格
	$sql = "SELECT order_id,send_id FROM $response_list WHERE store='{$store}' AND order_line = '2'";
	$res4 = $db->getAll($sql);

	foreach ($res4 as $value) {
		$order_id = $value['order_id'];
		play_order_price($station,$response_list,$response_info,$order_id);
	}

	//查询所有合单号,运费计算
	$sql = "SELECT send_id FROM $response_list WHERE store='{$store}' AND send_id LIKE 'H%' AND order_line = '2' GROUP BY send_id";
	$res5 = $db->getAll($sql);
	foreach ($res5 as $value) {
		$send_id = $value['send_id'];
		play_yf_code($station,$response_list,$response_info,$send_id);
		//日志
		$sql = "SELECT id FROM $response_list WHERE send_id = '{$send_id}'";
		$res = $db->getAll($sql);
		foreach ($res as $value) {
			$oms_id = $value['id'];
			$do = '[本单合单]：'.$send_id;
			oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
		}
	}

	// 日志
	if($all_one ==''){
		$do = '[合单]：本次无合单';
	}else{
		$do = '[合单]：'.$all_one;
	}
	$play = $station.'_order';
	oms_log($u_name,$do,$play,$station,$store,'-');

	echo json_encode($res3);
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
	$response_info = $station.'_response_info';
	if($station == 'amazon'){
		$cct = 'amz';
	}
	if($station == 'rakuten'){
		$cct = 'rku';
	}
	if($station == 'yahoo'){
		$cct = 'yho';
	}

	$sql = "SELECT id,order_id FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getAll($sql);

	// 单号回执
	$sql = "UPDATE $response_list SET send_id = concat('{$cct}',id) WHERE send_id = '{$send_id}'";
	$res2 = $db->execute($sql);

	// 计算金额
	foreach ($res as $value) {
		$id = $value['id'];
		$order_id = $value['order_id'];
		play_order_price($station,$response_list,$response_info,$order_id);

		// 查询新的send_id
	 	$sql = "SELECT send_id FROM $response_list WHERE id = '{$id}'";
		$res = $db->getOne($sql);
		$send_id = $res['send_id'];

		// 运费回滚
		play_yf_code($station,$response_list,$response_info,$send_id);

		// 日志
		$oms_id = $id;
		$do = '[拆单]：'.$_GET['break_common_order'];
		oms_log($u_name,$do,'change_order',$station,$store,$oms_id);

	}

	echo 'ok';
}

//扣库存
if(isset($_POST['sub_repo'])){
	$today = date('y-m-d',time()); //获取日期
	
	$now_station = strtolower($_POST['station']);
	$sub_repo = strtolower($_POST['sub_repo']);

	// 清空冻结订单表
	$sql = "TRUNCATE repo_pause";
	$res = $db->execute($sql);

	if($now_station == 'all_station'){
		if($sub_repo == 'common'){
			// 扣冻结表里的合单
			$sql = "SELECT id,station,send_id FROM amazon_response_list WHERE order_line = 3 AND send_id LIKE 'H%' UNION ALL SELECT id,station,send_id FROM rakuten_response_list WHERE order_line = 3 AND send_id LIKE 'H%' UNION ALL SELECT id,station,send_id FROM p_yahoo_response_list WHERE order_line = 3 AND send_id LIKE 'H%'";	
		}else{
			$my_checked_items = $_POST['my_checked_items'];
			// 如果是所有平台扣库存，即冻结表
			$sql = "SELECT id,station,send_id FROM amazon_response_list WHERE order_line = 3 AND order_id in ($my_checked_items) UNION ALL SELECT id,station,send_id FROM rakuten_response_list WHERE order_line = 3 AND order_id in ($my_checked_items) UNION ALL SELECT id,station,send_id FROM p_yahoo_response_list WHERE order_line = 3 AND order_id in ($my_checked_items)";	
		}

	}else{
		// 单个平台正常店铺发货
		$store = $_POST['store'];
		$now_response_list = $now_station.'_response_list';

		$sql = "SELECT station,send_id FROM $now_response_list WHERE order_line = 2 AND store = '{$store}' GROUP BY send_id";
	}

	// 按照 send_id 扣库存	
	$res = $db->getAll($sql);

	if(empty($res)){
		echo 'ok';die;
	}

	foreach ($res as $val) {
		$station = $val['station'];
		$response_list = $val['station'].'_response_list';
		$response_info = $val['station'].'_response_info';
		$send_id = $val['send_id'];
		// 查询send_id 的order_line
		$sql = "SELECT order_line FROM $response_list WHERE send_id = '{$send_id}'";
		$res = $db->getAll($sql);
		$ooddll = '1';
		foreach ($res as $value) {
			$order_line = $value['order_line'];
			if($order_line == '1' or $order_line == '-2' or $order_line == '-3' or $order_line == '-4' or $order_line == '-5' or $order_line == '9' or $order_line == '0'){
				$ooddll = '0';
			}
		}

		if($ooddll == '0')continue;


		// 查询出每个 send_id 对应的 order_id 的 商品代码
		$sql = "SELECT info.store,info.order_id as order_id,info.id as info_id,info.goods_code as goods_code,info.pause_ch as pause_ch,info.pause_jp as pause_jp,info.goods_num as goods_num FROM $response_info info,$response_list list WHERE list.order_id = info.order_id AND list.send_id = '{$send_id}'";
		$res2 = $db->getAll($sql);

		// 默认可发货
		$can_send = 1;
		$order_ids = '';
		$store = '';
		//遍历出订单
		foreach ($res2 as $val2) {
			$now_order_id = $val2['order_id'];
			$now_goods_code = $val2['goods_code'];
			$u_goods_num = $val2['goods_num'];
			$pause_ch = $val2['pause_ch'];
			$pause_jp = $val2['pause_jp'];
			$store = $val2['store'];

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
			// 冻结时间
			$now_time = time();
			// 更新冻结时间
			$sql = "SELECT pause_time FROM $response_list WHERE send_id = '{$send_id}'";
			$res = $db->getOne($sql);
			$pause_time = $res['pause_time'];
			if($pause_time == 0){
				$sql = "UPDATE $response_list SET pause_time = '{$now_time}' WHERE send_id = '{$send_id}'";
				$res = $db->execute($sql);
			}

			// 冻结订单表
			$sql = "SELECT order_id FROM $response_list WHERE send_id = '{$send_id}'";
			$res = $db->getAll($sql);
			foreach ($res as $value) {
				$order_id = $value['order_id'];
				$sql = "INSERT INTO repo_pause (pause_order) values ('{$order_id}')";
				$res = $db->execute($sql);
			}

			//日志
			$sql = "SELECT id FROM $response_list WHERE send_id = '{$send_id}'";
			$res = $db->getAll($sql);
			foreach ($res as $value) {
				$oms_id = $value['id'];
				$do = '[订单冻结]：'.$send_id;
				oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
			}
		}else if($can_send == 1){
			// 新需求：如果中国能发，则都从中国发 ---------------------------------------------------
			$order_ids = rtrim($order_ids,",");
			// 先检测这单是否有日本和中国订单
			$sql = "SELECT sum(pause_ch) AS sum_ch,sum(pause_jp) AS sum_jp FROM $response_info WHERE order_id in ($order_ids)";
			$res = $db->getOne($sql);
			$sum_ch = $res['sum_ch'];
			$sum_jp = $res['sum_jp'];
			if($sum_jp > 0 AND $sum_ch >0){	// 如果押中国，同时也押日本
				// 遍历所有购买日本的商品、数量
				$sql = "SELECT id,goods_code,pause_jp FROM $response_info WHERE order_id in ($order_ids)";
				$res = $db->getAll($sql);
				foreach ($res as $value) {
					$pp_id = $value['id'];
					$pp_goods_code = $value['goods_code'];
					$pp_pause_jp = $value['pause_jp'];
					if($pp_pause_jp == 0){
						// 如果该单押库存为0，跳过
					}else{
						// 查询中国是否有库存数
						$sql = "SELECT a_repo FROM goods_type WHERE goods_code = '{$pp_goods_code}'";
						$res = $rdb->getOne($sql);
						$pp_a_repo = $res['a_repo'];
						// 如果中国有库存
						if($pp_a_repo > $pp_pause_jp){
							// 消耗掉中国库存
							$sql = "UPDATE goods_type SET a_repo = a_repo - $pp_pause_jp WHERE goods_code = '{$pp_goods_code}'";
							$res = $rdb->execute($sql);
							// 还日本库存
							$sql = "UPDATE goods_type SET b_repo = b_repo + $pp_pause_jp WHERE goods_code = '{$pp_goods_code}'";
							$res = $rdb->execute($sql);
							// 押中国，押日本0
							$sql = "UPDATE $response_info SET pause_ch = pause_ch + $pp_pause_jp,pause_jp = 0 WHERE id = '{$pp_id}'";
							$res = $db->execute($sql);
						}
					}
				}
			}
			// 更新order_line
			$sql = "UPDATE $response_list SET order_line = '4' WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
			//日志
			$sql = "SELECT id FROM $response_list WHERE send_id = '{$send_id}'";
			$res = $db->getAll($sql);
			foreach ($res as $value) {
				$oms_id = $value['id'];
				$do = '[订单到发货区]：'.$send_id;
				oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
			}
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
		want_date,	#指定配送日
		want_time,	#指定配送时间
		import_day) SELECT	#导入日期 
		'amazon',
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
		want_date,
		want_time,
		'{$today}' from amazon_response_list list,amazon_response_info info where list.order_id = info.order_id AND list.order_line = '4'";
	$res = $db->execute($sql);

	// 雅虎转入发货表 !!!!!!!!!!!!!!!!!!!!!!!!!!!

	// 乐天转入发货表
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
		want_date,	#指定配送日
		want_time,	#指定配送时间
		import_day) SELECT	#导入日期 
		'rakuten',
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
		list.send_method,
		list.buyer_email,
		list.store,
		'{$u_name}',
		want_date,
		want_time,
		'{$today}' from rakuten_response_list list,rakuten_response_info info where list.order_id = info.order_id AND list.order_line = '4'";
	$res = $db->execute($sql);

		// 拍卖转入发货表
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
		want_date,	#指定配送日
		want_time,	#指定配送时间
		import_day) SELECT	#导入日期 
		'p_yahoo',
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
		list.send_method,
		list.buyer_email,
		list.store,
		'{$u_name}',
		want_date,
		want_time,
		'{$today}' from p_yahoo_response_list list,p_yahoo_response_info info where list.order_id = info.order_id AND list.order_line = '4'";
	$res = $db->execute($sql);

	// 更新order_line
	$sql = "UPDATE amazon_response_list SET order_line = '5' WHERE order_line = '4'";
	$res = $db->execute($sql);
	// $sql = "UPDATE yahoo_response_list SET order_line = '5' WHERE order_line = '4'";
	// $res = $db->execute($sql);
	$sql = "UPDATE rakuten_response_list SET order_line = '5' WHERE order_line = '4'";
	$res = $db->execute($sql);
	$sql = "UPDATE p_yahoo_response_list SET order_line = '5' WHERE order_line = '4'";
	$res = $db->execute($sql);
	echo 'ok';

}