<?php
require_once("../header.php");
require_once("../log.php");
require_once("./play_yfcode.php");
require_once("./play_price.php");

$dir = dirname(__FILE__);

set_time_limit(0);

// --- 搜索邮编查询 ---
if(isset($_GET['search_oms_post'])){
	$search_oms_post = addslashes($_GET['search_oms_post']);
	$sql = "SELECT * FROM oms_post WHERE post_code LIKE '%{$search_oms_post}%' LIMIT 0,100";
	$res = $db->getAll($sql);
	echo json_encode($res);
}
if(isset($_GET['search_oms_addr'])){
	$search_oms_addr = addslashes($_GET['search_oms_addr']);
	$sql = "SELECT * FROM oms_post WHERE post_name LIKE '%{$search_oms_addr}%' LIMIT 0,100";
	$res = $db->getAll($sql);
	echo json_encode($res);
}
// --- 搜索邮编查询 ---

// 查询运费代码信息
if(isset($_GET['read_yfcode'])){
	$info_id = $_GET['info_id'];
	$station = $_GET['station'];
	$response_info = $station.'_response_info';
	$yfcode = $_GET['read_yfcode'];
	$sql = "SELECT * FROM yf_code WHERE yf_code_name = '{$yfcode}'";
	$res = $db->getOne($sql);
	$status = $res['status'];
	$level = $res['level'];
	$send_method = $res['send_method'];
	if($status == 1){
		$status = '已开启';
	}else{
		$status = '已关闭';
	}
	if(empty($res)){
		$yf_info = "无此运费代码！";
	}else{
		// $yf_info = $status.' / 优先级：'.$level.' / 配送方式：'.$send_method;	
		$yf_info = $send_method;	
	}
	$sql = "UPDATE $response_info SET yf_info = '{$yf_info}' WHERE id = '{$info_id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 获取邮编地址结果
if(isset($_GET['read_oms_post'])){
	$post_code = $_GET['read_oms_post'];
	$sql = "SELECT post_name FROM oms_post WHERE post_code = '{$post_code}'";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 获取未验证订单数
if(isset($_GET['need_check_num'])){
	$store = $_GET['need_check_num'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$sql = "SELECT count(1) as need_check_num FROM $response_list WHERE (post_ok=0 OR tel_ok=0 OR sku_ok=0 OR yfcode_ok=0) AND store = '{$store}'";
	$res = $db->getOne($sql);
	echo $res['need_check_num'];
}

//检测商品代码
if(isset($_GET['check_goods_code'])){
	$check_goods_code = addslashes($_GET['check_goods_code']);
	$sql = "SELECT 1 FROM goods_type WHERE goods_code='{$check_goods_code}' limit 1";
    $res = $rdb->getOne($sql);
    if(empty($res)){
    	echo 'error';
    }else{
    	echo 'ok';
    }
}

//商品代码提示
if(isset($_GET['tip_goods_code'])){
	$tip_goods_code = addslashes($_GET['tip_goods_code']);
	$sql = "SELECT goods_code FROM goods_type WHERE goods_code LIKE '%{$tip_goods_code}%' limit 10";
    $res = $rdb->getAll($sql);
    echo json_encode($res);
}

// AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA 总体验证开始 AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
if(isset($_GET['check_all_field'])){
	$store = $_GET['check_all_field'];
	$station = strtolower($_GET['station']);

	$db = new PdoMySQL();
	$rdb = new RepoPdoMySQL();
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

// 11111111111111111111 验证邮编地址开始 11111111111111111111
	// 搜出本店铺未验证的地址的订单号 post_ok=0
	$sql = "SELECT order_id FROM $response_list WHERE store = '{$store}' AND post_ok = 0";
	$res = $db->getAll($sql);

	foreach ($res as $value) {
		$now_order_id = $value['order_id'];
		play_order_price($station,$response_list,$response_info,$now_order_id);
		// 查询客人填写的post和addr
		$sql = "SELECT post_code,address FROM $response_list WHERE order_id = '{$now_order_id}'";
		$res = $db->getOne($sql);
		$post_code = $res['post_code'];
		$address = $res['address'];
		// 拿去匹配
		$sql = "SELECT post_name FROM oms_post WHERE post_code = '{$post_code}'";
		$res = $db->getAll($sql);
		if(empty($res)){
			$sql = "UPDATE $response_list SET post_ok = 2 WHERE order_id = '{$now_order_id}'";
			$res = $db->execute($sql);
		}else{
			$has_post = 0;
			foreach ($res as $value) {
				$post_name = $value['post_name'];
				if(strpos($address, $post_name)!==false){
					$has_post = 1;

				}else{
					//一个邮编可能会有多个地址
					if($has_post==1){
						$has_post = 1;
					}else{
						$has_post = 0;
					}
				}
			}

			//如果通过，更新order_line
			if($has_post == 1){
				$sql = "UPDATE $response_list SET post_ok = 1 WHERE order_id = '{$now_order_id}'";
				$res = $db->execute($sql);
			}else if($has_post == 0){
				$sql = "UPDATE $response_list SET post_ok = 2 WHERE order_id = '{$now_order_id}'";
				$res = $db->execute($sql);
			}
		}
	}
// 11111111111111111111 验证邮编地址结束 11111111111111111111

// 22222222222222222222 检测电话是否填写开始 22222222222222222222
	// 搜出本店铺未验证的电话的订单号 tel_ok=0
	$sql = "SELECT order_id FROM $response_list WHERE store = '{$store}' AND tel_ok = 0";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$now_order_id = $value['order_id'];

		//检测电话是否填写
		$sql = "SELECT phone FROM $response_list WHERE order_id = '{$now_order_id}'";
		$res = $db->getOne($sql);
		if($res['phone']==''){
			$sql = "UPDATE $response_list SET tel_ok = 2 WHERE order_id = '{$now_order_id}'";
			$res = $db->execute($sql);
		}else{
			$sql = "UPDATE $response_list SET tel_ok = 1 WHERE order_id = '{$now_order_id}'";
			$res = $db->execute($sql);
		}
	}
// 22222222222222222222 检测电话是否填写结束 22222222222222222222

// 33333333333333333333 检测SKU 如果是福袋则拆开始 33333333333333333333
	// 搜出本店铺未验证的sku sku_ok=0
	$sql = "SELECT id,sku FROM $response_info WHERE sku_ok = 0 AND store = '{$store}'";
	$res = $db->getAll($sql);

	//遍历所有sku
	foreach ($res as $value){
		$now_id = $value['id'];	#正在检测id
		$now_goods = $value['sku'];	#福袋名/别名、商品代码
		$new_name = $now_goods;

		//格式化短横线
	 	$count_line = substr_count($new_name,'-');
		$replace_line = '--';
		for($i=0;$i<$count_line;$i++){
			$new_name = str_replace($replace_line,"-",$new_name);
		}
		
		// 拆亚马逊CSP
		$arr = explode('-CSP0', $new_name);
		$new_name = $arr[0];

		// echo $new_name;
		if($new_name != $now_goods){
			$sql = "UPDATE $response_info SET goods_code = '{$new_name}' WHERE id = '{$now_id}'";
		}else{
			$sql = "UPDATE $response_info SET goods_code = sku WHERE id = '{$now_id}'";
		}
		$res = $db->execute($sql);

		// SKU更正
		$sql = "SELECT goods_code FROM true_sku WHERE sku = '{$new_name}' AND store = '{$store}'";
		$res = $db->getOne($sql);
		$true_sku = $res['goods_code'];
		if(empty($res)){

		}else{
			$sql = "UPDATE $response_info SET goods_code = '{$true_sku}' WHERE id = '{$now_id}'";
			$res = $db->execute($sql);
			$new_name = $true_sku;
		}

		// 拆福袋
		$sql = "SELECT count(1) FROM new_name WHERE new_name='{$new_name}'";
	    $res = $rdb->getOne($sql);
	    $count_new = $res['count(1)'];
	    if($count_new > 0){	//如果是福袋/别名
	    	$sql = "SELECT goods_code FROM new_name WHERE new_name='{$new_name}' order by id";
	    	$res = $rdb->getAll($sql);
	    	//生成随机标识符
	    	$rand_key = rand(10000,99999);
	    	foreach ($res as $val) {
	    		// 雅虎平台商品选项
		    	if($station == 'yahoo'){
		    		//拆单、并且sku通过sku_ok = 1
		    		$sql = "INSERT INTO $response_info (
		    		station,
		    		store,
		    		order_id,
		    		holder,
		    		is_back,
		    		goods_title,
		    		sku_ok,
		    		yfcode_ok,
		    		yfcode,
		    		sku,
		    		goods_code,
		    		goods_option,
		    		goods_info,
		    		goods_id,
		    		goods_num,
		    		unit_price,
		    		is_pause,
		    		item_price,
		    		item_tax,
		    		import_time,
		    		cod_money) SELECT 
		    		station,
					store,
		    		order_id,
		    		'{$rand_key}',
		    		is_back,
		    		goods_title,
		    		'1',
		    		yfcode_ok,
		    		yfcode,
		    		sku,
		    		'{$val['goods_code']}',
		    		goods_option,
		    		goods_info,
		    		goods_id,
		    		goods_num,
		    		unit_price,
		    		is_pause,
		    		item_price,
		    		item_tax,
		    		import_time,
		    		cod_money
		    		 FROM $response_info WHERE id = '{$now_id}'";
		    		$res = $db->execute($sql);
		    	}else{
		    		//拆单、并且sku通过sku_ok = 1
		    		$sql = "INSERT INTO $response_info (
		    		station,
		    		store,
		    		order_id,
		    		holder,
		    		is_back,
		    		goods_title,
		    		sku_ok,
		    		yfcode_ok,
		    		yfcode,
		    		sku,
		    		goods_code,
		    		goods_num,
		    		unit_price,
		    		is_pause,
		    		item_price,
		    		item_tax,
		    		import_time,
		    		cod_money) SELECT 
		    		station,
					store,
		    		order_id,
		    		'{$rand_key}',
		    		is_back,
		    		goods_title,
		    		'1',
		    		yfcode_ok,
		    		yfcode,
		    		sku,
		    		'{$val['goods_code']}',
		    		goods_num,
		    		unit_price,
		    		is_pause,
		    		item_price,
		    		item_tax,
		    		import_time,
		    		cod_money
		    		 FROM $response_info WHERE id = '{$now_id}'";
		    		$res = $db->execute($sql);
		    	}
	    	}
	    	// 查询该单订单号
	    	$sql = "SELECT order_id FROM $response_info WHERE id = '{$now_id}'";
	    	$res = $db->getOne($sql);
	    	$nn_order_id = $res['order_id'];

	    	// 删除原福袋item
	    	$sql = "DELETE FROM $response_info WHERE id = '{$now_id}'";
	    	$res = $db->execute($sql);

	    	// 清空单价为0
	    	$sql = "SELECT id FROM $response_info WHERE order_id = '{$nn_order_id}' AND holder = '{$rand_key}'";
	    	$res = $db->getAll($sql);
	    	$k = 0;
	    	foreach ($res as $value) {
	    		if($k > 0){
	    			$nn_id = $value['id'];
		    		$sql = "UPDATE $response_info SET unit_price = '0',item_price = '0' WHERE id = '{$nn_id}'";
		    		$res = $db->execute($sql);
	    		}
	    		$k = $k + 1;
	    	}
	    }else{	//如果不是福袋，则检测 商品代码 是否存在
	    	$sql = "SELECT 1 FROM goods_type WHERE goods_code='{$new_name}' limit 1";
	        $res = $rdb->getOne($sql);
	        if(empty($res)){
	        	//验证不通过	sku_ok = 2
	        	$sql = "UPDATE $response_info SET sku_ok = 2 WHERE id = '{$now_id}'";
	        	$res = $db->execute($sql);
	        }else{
	        	//验证通过 sku_ok = 1
	        	$sql = "UPDATE $response_info SET sku_ok = 1 WHERE id = '{$now_id}'";
	        	$res = $db->execute($sql);
	        }
	    }
	}
	//遍历完所有SKU、LIST表 sku_ok 运算结果
	$sql = "SELECT order_id FROM $response_list WHERE store = '{$store}' AND sku_ok = 0";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$now_order_id = $value['order_id'];
		// 查询总item 数
		$sql = "SELECT count(1) as ycm FROM $response_info WHERE order_id = '{$now_order_id}'";
		$res = $db->getOne($sql);
		$item_count = $res['ycm'];
		// 查询sku_ok item数
		$sql = "SELECT count(1) as bcd FROM $response_info WHERE order_id = '{$now_order_id}' AND sku_ok = 1";
		$res1 = $db->getOne($sql);
		$sku_ok_count = $res1['bcd'];
		if($item_count == $sku_ok_count){
			//更新list sku_ok = 1  通过
			$sql = "UPDATE $response_list SET sku_ok = 1 WHERE order_id = '{$now_order_id}'";
			$res = $db->execute($sql);
		}else{
			//更新list sku_ok = 2  不通过
			$sql = "UPDATE $response_list SET sku_ok = 2 WHERE order_id = '{$now_order_id}'";
			$res = $db->execute($sql);
		}
	}
// 33333333333333333333 检测SKU 如果是福袋则拆结束 33333333333333333333
// 44444444444444444444 运费代码验证开始 44444444444444444444
	if($station == 'amazon'){
		// 如果是亚马逊，则默认全部宅配
		$sql = "UPDATE $response_info SET yfcode='宅配便' WHERE yfcode_ok = 0";
		$res = $db->execute($sql);

		// 亚马逊么有运费代码 过

		$sql = "UPDATE $response_info SET yfcode_ok=1 WHERE yfcode_ok = 0";
		$res = $db->execute($sql);

		$sql = "UPDATE $response_list SET yfcode_ok=1 WHERE yfcode_ok = 0";
		$res = $db->execute($sql);
	}else{
		// 合单ID进行计算
		$sql = "SELECT send_id FROM $response_list WHERE store = '{$store}' AND yfcode_ok = 0 ORDER BY id";
		$res = $db->getAll($sql);
		foreach ($res as $value) {
			$now_send_id = $value['send_id'];
			// 因为合单要调用此函数，故取合单号计算
			play_yf_code($station,$response_list,$response_info,$now_send_id);

			//计算后再卡
			$sql = "SELECT order_id FROM $response_list WHERE send_id = '{$now_send_id}'";
			$res = $db->getOne($sql); //第一次，所以订单号与send_id 一一对应。
			$now_order_id = $res['order_id'];
			// 查询总item 数
			$sql = "SELECT count(1) as ycm FROM $response_info WHERE order_id = '{$now_order_id}'";
			$res = $db->getOne($sql);
			$item_count = $res['ycm'];
			// 查询yfcode_ok item数
			$sql = "SELECT count(1) as bcd FROM $response_info WHERE order_id = '{$now_order_id}' AND yfcode_ok = 1";
			$res1 = $db->getOne($sql);
			$sku_ok_count = $res1['bcd'];
			if($item_count == $sku_ok_count){
				//更新list yfcode_ok = 1  通过
				$sql = "UPDATE $response_list SET yfcode_ok = 1 WHERE order_id = '{$now_order_id}'";
				$res = $db->execute($sql);
			}else{
				//更新list yfcode_ok = 2  不通过
				$sql = "UPDATE $response_list SET yfcode_ok = 2 WHERE order_id = '{$now_order_id}'";
				$res = $db->execute($sql);
			}

		}

	}
// 44444444444444444444 运费代码验证结束 44444444444444444444
	echo 'ok';
}
// AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA 总体验证结束 AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA

// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB 列表字段验证开始 BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB

if(isset($_GET['need_check_list'])){
	$store = $_GET['need_check_list'];
	$station = strtolower($_GET['station']);
	$field_name = $_GET['field_name'];
	$order_id = $_GET['order_id'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';

	//	收件人/电话/地址/运费/消费税/积分
	if($field_name == 'receive_name' or $field_name == 'order_tax' or $field_name == 'pay_money' or $field_name == 'phone' or $field_name == 'buyer_email' or $field_name == 'shipping_price' or $field_name == 'payment_method' or $field_name == 'points'){
		echo 'ok';
	}

}

// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB 列表字段验证结束 BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB

// 邮编、地址验证。通过则更新。
if(isset($_GET['change_post_addr'])){
	$store = $_GET['change_post_addr'];
	$station = strtolower($_GET['station']);
	$order_id = $_GET['order_id'];
	$response_list = $station.'_response_list';
	$new_post_code = addslashes($_GET['new_post_code']);
	$new_address = addslashes($_GET['new_address']);

	// 查询出该 post_code 的对应市区
	$sql = "SELECT post_name FROM oms_post WHERE post_code = '{$new_post_code}'";
	$res = $db->getAll($sql);
	
	$pass = 0;
	foreach ($res as $value) {
		$post_name = $value['post_name'];
		// 去掉括弧里面的地址
		if(strstr($post_name,'（')==true){
			$res = explode('（',$post_name);
			$post_name = $res[0];
		}
		if(strstr($new_address, $post_name) == true){
			$pass = 1;
		}
	}
	
	if($pass == 0){
		echo '邮编/地址不匹配。';
	}else{
		// 邮编和地址匹配
		if($pass == 1){
			// 查询原字段值
			$sql = "SELECT post_code,address FROM $response_list WHERE order_id = '{$order_id}'";
			$res = $db->getOne($sql);
			$o_post_code = $res['post_code'];
			$o_address = $res['address'];

			// 保存并更新post_ok = 1
			$sql = "UPDATE $response_list SET post_code = '{$new_post_code}',address = '{$new_address}',post_ok = 1 WHERE order_id = '{$order_id}'";
		    $res = $db->execute($sql);

		    //查询OMS-ID
			$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
			$res = $db->getOne($sql);
			$oms_id = $res['id'];

		    // 日志
			$do = '订单【'.$order_id.'】修改 <邮编/地址>【'.$o_post_code.'/'.$o_address.'】为【'.$new_post_code.'/'.$new_address.'】';
			oms_log($u_name,$do,'change_order',$station,$store,$oms_id);

			echo 'ok';
		}else{
			echo '邮编、地址不匹配。';
		}
	}
}
