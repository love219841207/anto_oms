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
	$store = $_GET['store'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';
	$sql = "SELECT send_id FROM $response_list WHERE order_line = '2' GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$send_id = $val['send_id'];
		//查询出每个send_id 对应的 order_id 的SKU
		$sql = "SELECT list.order_id,info.goods_code as goods_code,info.goods_num as goods_num FROM $response_info info,$response_list list WHERE list.order_id = info.order_id AND list.send_id = '{$send_id}'";
		$res2 = $db->getAll($sql);
		$can_send = 1;
		//遍历出订单
		foreach ($res2 as $val2) {
			 // $val2['order_id'];
			$now_goods_code = $val2['goods_code'];
			$goods_num = $val2['goods_num'];
			$goods_num;

			// 查询是否有库存
			$sql = "SELECT b_repo FROM goods_type WHERE goods_code = '{$now_goods_code}'";
			$res = $rdb->getOne($sql);

			if($goods_num > $res['b_repo']){
				//无货
				$can_send = 0;
			}else{
				//有货
			}
		}
		if($can_send == 0){
			//没有货冻结订单
			$sql = "UPDATE $response_list SET order_line = '3' WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}else if($can_send == 1){
			//有货发出
			foreach ($res2 as $val2) {
				//扣库存
			 	$now_order_id = $val2['order_id'];
				$now_goods_code = $val2['goods_code'];
				$goods_num = $val2['goods_num'];
				$sql = "UPDATE goods_type SET b_repo = b_repo - $goods_num WHERE goods_code = '{$now_goods_code}'";
				$res = $rdb->execute($sql);

				//扣掉库存后记录库存系统流水
				$sql = "INSERT INTO jp_list_out (order_id,goods_code,out_num,oms_id,store_name,holder,out_day,import_day) VALUES ('{$now_order_id}','{$now_goods_code}','{$goods_num}','{$send_id}','{$store}','{$u_name}','{$today}','{$today}')";
				$res = $rdb->execute($sql);
			}

			// 更新order_line
			$sql = "UPDATE $response_list SET order_line = '4' WHERE send_id = '{$send_id}'";
			$res = $db->execute($sql);
		}	
	}
	//转入发货表
	$sql = "INSERT INTO send_table (
		station,
		send_id,	#合单发货ID
		oms_id,	#OMS-ID
		info_id, #info-ID
		sku, 	#sku，客人看
		goods_code,	#商品代码，仓库看
		out_num,	#商品数量
		who_tel,	#配送电话
		who_post,	#邮编
		who_house,	#地址
		who_name,	#收货人
		is_cod,		#是否代引
		due_money,	#代引金额，写出全部的item金额，根据cod，更新是否是代引
		express_company,
		send_method,
		who_email,	#邮编
		store_name,	#店铺名
		holder,		#担当者
		import_day) SELECT	#导入日期 
		'{$station}',
		list.send_id,
		list.id,
		info.id,
		info.sku,
		info.goods_code,
		info.goods_num,
		list.phone,
		list.post_code,
		list.address,
		list.receive_name,
		list.payment_method,
		list.pay_money,	#带引金额
		'佐川急便',	#默认左川
		info.yfcode,
		list.buyer_email,
		'{$store}',
		'{$u_name}',
		'{$today}' from $response_list list,$response_info info where list.order_id = info.order_id AND list.order_line = '4' AND list.store = '{$store}'";
	$res = $db->execute($sql);

	//合单代引金额计算
	// $sql = "UPDATE send_table aa,send_table bb  SET aa.due_money = sum(aa.due_money) WHERE aa.send_id=bb.send_id";
	// $res = $db->execute($sql);

	//地址分配配送公司，更新黑猫地址	（神奈川県，埼玉県，茨城県，群馬県，山梨県）
	$sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = '宅急便' WHERE who_house LIKE '%神奈川県%' OR who_house LIKE '%埼玉県%' OR who_house LIKE '%茨城県%' OR who_house LIKE '%群馬県%' OR who_house LIKE '%山梨県%'";
	$res = $db->execute($sql);

	// 更新order_line
	$sql = "UPDATE $response_list SET order_line = '5' WHERE order_line = '4' AND store = '{$store}'";
	$res = $db->execute($sql);
	echo 'ok';

}