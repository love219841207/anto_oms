<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 修改快递公司
if(isset($_GET['change_company'])){
	$order_id = $_GET['change_company'];
	$new_company = $_GET['new_company'];
	$new_method = $_GET['new_method'];
	$station = $_GET['station'];
	$store = $_GET['store'];
	$oms_id = $_GET['oms_id'];

	// 重置快递公司 和包裹
	$sql = "UPDATE send_table SET express_company = '{$new_company}',send_method = '{$new_method}' WHERE order_id = '{$order_id}'";
	$res = $db->execute($sql);

	$do = "<订单>【".$order_id."】"."修改<快递公司>为【".$new_company."】<配送方式>为【".$new_method."】";
	oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

	echo 'ok';
}

// 重置express
if(isset($_GET['reset_express'])){
	// 重置快递公司 和包裹
	$sql = "UPDATE send_table SET express_company ='',pack_id = ''";
	$res = $db->execute($sql);

	// 重置配送方式
	$sql = "UPDATE send_table SET send_method = '宅配便' WHERE send_method = '宅急便'";
	$res = $db->execute($sql);

	// item_line
	$sql = "UPDATE send_table SET item_line = '0'";
	$res = $db->execute($sql);

	echo 'ok';
}

// 打包
if(isset($_GET['packing'])){
	// 如果是 mail便，查询出，遍历进行体积运算，分pack_id(包裹数，oms_id-1,2,x)

	// pack_id
	$sql = "UPDATE send_table SET pack_id = oms_id";
	$res = $db->execute($sql);

	// 如果是合单
	$sql = "SELECT send_id FROM send_table WHERE item_line = '0' AND send_id LIKE 'H%' GROUP BY send_id";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		// 更新为一个pack_id
		$now_send_id = $value['send_id'];
		// 查询出第一个OMS-ID
		$sql = "SELECT oms_id FROM send_table WHERE send_id = '{$now_send_id}' LIMIT 1";
		$res = $db->getOne($sql);
		$h_oms_id = $res['oms_id'];
		$sql = "UPDATE send_table SET pack_id = '{$h_oms_id}' WHERE send_id = '{$now_send_id}'";
		$res = $db->execute($sql);
	}

	// 包裹ID分平台
	$sql = "UPDATE send_table SET pack_id = concat('11',pack_id) WHERE station = 'amazon'";
	$res = $db->execute($sql);
	$sql = "UPDATE send_table SET pack_id = concat('22',pack_id) WHERE station = 'yahoo'";
	$res = $db->execute($sql);
	$sql = "UPDATE send_table SET pack_id = concat('33',pack_id) WHERE station = 'rakuten'";
	$res = $db->execute($sql);

	//先变成佐川
	$sql = "UPDATE send_table SET express_company ='佐川急便' WHERE send_method = '宅配便'";
	$res = $db->execute($sql);

	//地址分配配送公司，更新黑猫地址	（神奈川県，埼玉県，茨城県，群馬県，山梨県）
	$sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = '宅急便' WHERE send_method = '宅配便' AND who_house LIKE '%神奈川県%' OR who_house LIKE '%埼玉県%' OR who_house LIKE '%茨城県%' OR who_house LIKE '%群馬県%' OR who_house LIKE '%山梨県%'";
	$res = $db->execute($sql);

	// item_line
	$sql = "UPDATE send_table SET item_line = '1'";
	$res = $db->execute($sql);

	echo 'ok';
}

// 读取待发货
if(isset($_GET['show_send_info'])){
	$id = $_GET['show_send_info'];
	$sql = "SELECT * FROM send_table WHERE id = '{$id}'";
	$res = $db->getAll($sql);

	echo json_encode($res);
}

// 查看库存数
if(isset($_GET['check_repo'])){
	$goods_code = $_GET['check_repo'];

	$sql = "SELECT a_repo,b_repo,a_repo+b_repo AS repo FROM goods_type WHERE goods_code = '{$goods_code}'";
	$res = $rdb->getOne($sql);
	$final_res['repo'] = $res['repo'];
	$final_res['a_repo'] = $res['a_repo'];
	$final_res['b_repo'] = $res['b_repo'];
	echo json_encode($final_res);
}

// 添加待发货item
if(isset($_POST['add_send_item'])){
	$today = date('y-m-d',time()); //获取日期
	$goods_code = trim(addslashes($_POST['add_send_item']));
	$order_id = $_POST['order_id'];
	$goods_num = $_POST['add_goods_num'];
	$add_unit_price = $_POST['add_unit_price'];
	$add_yfcode = $_POST['add_yfcode'];
	$add_cod_money = $_POST['add_cod_money'];
	$id = $_POST['id']; 	//复制该条信息的ID

	// 查询基础信息
	$sql = "SELECT * FROM send_table WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	$who_email = $res['who_email'];
	$who_tel = $res['who_tel'];
	$who_name = $res['who_name'];
	$who_post = $res['who_post'];
	$who_house = $res['who_house'];
	$is_cod = $res['is_cod'];
	$send_method = $res['send_method'];
	$station = $res['station'];
	$store = $res['store_name'];
	$oms_id = $res['oms_id'];
	$send_id = $res['send_id'];
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	//运费金额计算 ###############
	$add_yf_money = '0';	//暂时为0

	// 金额计算
	if($is_cod == 'COD'){
		$due_money = $add_unit_price * $goods_num + $add_cod_money + $add_yf_money;
	}else{
		$due_money = 0;
		$add_cod_money = 0;
	}

	// 查询库存数
	$sql = "SELECT a_repo+b_repo AS repo FROM goods_type WHERE goods_code = '{$goods_code}'";
	$res = $rdb->getOne($sql);
	$leave_num = $res['repo'] - $goods_num;

	if($leave_num < 0){
		echo '库存不足';die;
	}else{
		// 查询库存数
		$sql = "SELECT a_repo,b_repo FROM goods_type WHERE goods_code = '{$goods_code}'";
		$res = $rdb->getOne($sql);
		$a_repo = $res['a_repo'];
		$b_repo = $res['b_repo'];

		$pause_jp = 0;
		$pause_ch = 0;
		// 扣库存
		if($goods_num > $b_repo){
			// 消耗掉日本库存
			$sql = "UPDATE goods_type SET b_repo = 0 WHERE goods_code = '{$goods_code}'";
			$res = $rdb->execute($sql);

			$pause_jp = $b_repo;

			$need_num = $goods_num - $b_repo;

			//消耗中国库存
			$sql = "UPDATE goods_type SET a_repo = a_repo - $need_num WHERE goods_code = '{$goods_code}'";
			$res = $rdb->execute($sql);

			$pause_ch = $need_num;
		}else{
			// 数量小于等于日本，消耗日本库存
			$sql = "UPDATE goods_type SET b_repo = b_repo - $goods_num WHERE goods_code = '{$goods_code}'";
			$res = $rdb->execute($sql);

			$pause_jp = $goods_num;
		}

		// 当前时间戳
		$now_time = time();

		// 插入 resoponse_info
		$sql = "INSERT INTO $response_info (
			store,
			order_id,
			holder,
			goods_title,
			sku_ok,
			yfcode_ok,
			yfcode,
			yf_money,
			sku,
			goods_code,
			goods_num,
			is_pause,
			pause_ch,
			pause_jp,
			unit_price,
			cod_money,
			import_time) VALUES(
			'{$store}',
			'{$order_id}',
			'{$u_name}',
			'',
			'1',
			'1',
			'{$add_yfcode}',
			'{$add_yf_money}',
			'{$goods_code}',
			'{$goods_code}',
			'{$goods_num}',
			'pass',
			'{$pause_ch}',
			'{$pause_jp}',
			'{$add_unit_price}',
			'{$add_cod_money}',
			{$now_time}
			) ";
		$res = $db->execute($sql);

		// 查询 info_id
		$sql = "SELECT max(id) as info_id FROM $response_info WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);
		$info_id = $res['info_id'];

		// 插入send_table
		$sql = "INSERT INTO send_table (
			station,
			order_id,
			send_id,	#合单发货ID
			oms_id,	#OMS-ID
			info_id, #info-ID
			sku, 	#sku，客人看
			goods_code,	#商品代码，仓库看
			out_num,	#商品数量
			pause_ch,
			pause_jp,
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
			import_day) VALUES (
			'{$station}',
			'{$order_id}',
			'{$send_id}',
			'{$oms_id}',
			'{$info_id}',
			'{$goods_code}',
			'{$goods_code}',
			'{$goods_num}',
			'{$pause_ch}',
			'{$pause_jp}',
			'{$who_tel}',
			'{$who_post}',
			'{$who_house}',
			'{$who_name}',
			'{$is_cod}',
			'{$due_money}',
			'{$send_method}',
			'{$who_email}',
			'{$store}',
			'{$u_name}',
			'{$today}')";
		$res = $db->execute($sql);
	}

	// 如果COD_money大于0，则为代引
	if($add_cod_money > 0){
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$goods_code.'】数量【'.$goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】运费金额【'.$add_yf_money.'】代引金额【'.$add_cod_money.'】';

	}else{
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$goods_code.'】数量【'.$goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】运费金额【'.$add_yf_money.'】';
	}

	oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);
	$final_res['status'] = 'ok';
	$final_res['order_id'] = $order_id;
	$final_res['station'] = $station;
	$final_res['store'] = $store;
	echo json_encode($final_res);
}

// 删除待发货item
if(isset($_GET['del_send_item'])){
	$id = $_GET['del_send_item'];
	$oms_id = $_GET['oms_id'];
	$info_id = $_GET['info_id'];
	$store = $_GET['store'];
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	// 查 pause_ch,pause_jp 和 goods_code
	$sql = "SELECT goods_code,pause_jp,pause_ch,send_id FROM send_table WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	$goods_code = $res['goods_code'];
	$pause_jp = $res['pause_jp'];
	$pause_ch = $res['pause_ch'];
	$send_id = $res['send_id'];

	// 还库存
	$sql = "UPDATE goods_type SET a_repo = a_repo + $pause_ch,b_repo = b_repo + $pause_jp WHERE goods_code = '{$goods_code}'";
	$res = $rdb->execute($sql);

	// 删除 send_table
	$sql = "DELETE FROM send_table WHERE id = '{$id}'";
	$res = $db->execute($sql);

	// 查询删除的item
	$sql = "SELECT * FROM $response_info WHERE id = '{$info_id}'";
	$res = $db->getOne($sql);

	// 删除 info_table
	$sql = "DELETE FROM $response_info WHERE id = '{$info_id}'";
	$res1 = $db->execute($sql);

	$do = '[删除一单]：订单号【'.$res['order_id'].'】商品代码【'.$res['goods_code'].'】数量【'.$res['goods_num'].'】子订单价格【'.$res['item_price'].'】运费代码【'.$res['yfcode'].'】运费金额【'.$res['yf_money'].'】代引金额【'.$res['cod_money'].'】';

	oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

	$final_res['status'] = 'ok';
	$final_res['order_id'] = $res['order_id'];
	echo json_encode($final_res);
}

// 验证send字段
if(isset($_GET['need_check_send'])){
	$field_name = $_GET['field_name'];
	$new_key = $_GET['new_key'];

	//	收件人/电话/地址
	if($field_name == 'who_name' or $field_name == 'who_tel' or $field_name == 'who_email'){
		echo 'ok';
	}

	// 数量 or 代引金额
	if($field_name == 'out_num' or $field_name == 'due_money'){
		if($new_key < 0){
			echo '不能为负数。';
		}else{
			echo 'ok';
		}
	}

	// goods_code
	if($field_name == 'goods_code'){
		$sql = "SELECT 1 FROM goods_type WHERE goods_code='{$new_key}' limit 1";
	    $res = $rdb->getOne($sql);
	    if(empty($res)){
	    	echo '无此商品代码。';
	    }else{
	    	echo 'ok';
	    }
	}

}

//修改send字段
if(isset($_GET['change_send_field'])){
	$today = date('y-m-d',time()); //获取日期
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$id = $_GET['id'];	//send_table 的 ID
	$oms_id = $_GET['oms_id'];
	$info_id = $_GET['info_id'];
	$o_key = $_GET['o_key'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	if($field_name == 'who_tel'){
		$ch_field = '电话';
		$list_field = 'phone';
		// 更新LIST
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'who_name'){
		$ch_field = '收件人';
		$list_field = 'receive_name';
		// 更新LIST
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'who_email'){
		$ch_field = '邮箱';
		$list_field = 'buyer_email';
		// 更新LIST	
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'goods_code'){
		$ch_field = '商品代码';
		$list_field = 'goods_code';
		// 更新INFO
		$sql = "UPDATE $response_info SET $list_field = '{$new_key}' WHERE id = '{$info_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'out_num'){
		$ch_field = '数量';
		$list_field = 'goods_num';

		// 查询goods_code,原out_num
		$sql = "SELECT goods_code,out_num as o_num FROM send_table WHERE id = '{$id}'";
		$res = $db->getOne($sql);
		$goods_code = $res['goods_code'];
		$o_num = $res['o_num'];

		// 查询库存数
		$sql = "SELECT a_repo+b_repo AS repo FROM goods_type WHERE goods_code = '{$goods_code}'";
		$res = $rdb->getOne($sql);
		$leave_num = $res['repo'] + $o_num - $new_key;

		if($leave_num < 0){
			echo '库存不足';die;
		}else{
			// 查 pause_ch,pause_jp 和 goods_code
			$sql = "SELECT * FROM send_table WHERE id = '{$id}'";
			$res = $db->getOne($sql);
			$goods_code = $res['goods_code'];
			$pause_jp = $res['pause_jp'];
			$pause_ch = $res['pause_ch'];
			$send_id = $res['send_id'];

			// 还库存
			$sql = "UPDATE goods_type SET a_repo = a_repo + $pause_ch,b_repo = b_repo + $pause_jp WHERE goods_code = '{$goods_code}'";
			$res = $rdb->execute($sql);

			// info & send_table pause_ch,pause_jp
			$sql = "UPDATE send_table SET pause_ch = 0,pause_jp = 0 WHERE id = '{$id}'";
			$res = $db->execute($sql);
			$sql = "UPDATE $response_info SET pause_ch = 0,pause_jp = 0 WHERE id = '{$info_id}'";
			$res = $db->execute($sql);

			// 查询库存数
			$sql = "SELECT a_repo,b_repo FROM goods_type WHERE goods_code = '{$goods_code}'";
			$res = $rdb->getOne($sql);
			$a_repo = $res['a_repo'];
			$b_repo = $res['b_repo'];

			$goods_num = $new_key;

			// 扣库存
			if($goods_num > $b_repo){
				// 消耗掉日本库存
				$sql = "UPDATE goods_type SET b_repo = 0 WHERE goods_code = '{$goods_code}'";
				$res = $rdb->execute($sql);

				// info pause_jp
				$sql = "UPDATE $response_info SET pause_jp = $b_repo WHERE id = '{$info_id}'";
				$res = $db->execute($sql);

				// send_table pause_jp
				$sql = "UPDATE send_table SET pause_jp = $b_repo WHERE id = '{$id}'";
				$$res = $db->execute($sql);

				$need_num = $goods_num - $b_repo;

				//消耗中国库存
				$sql = "UPDATE goods_type SET a_repo = a_repo - $need_num WHERE goods_code = '{$goods_code}'";
				$res = $rdb->execute($sql);

				// info pause_ch
				$sql = "UPDATE $response_info SET pause_ch = $need_num WHERE id = '{$info_id}'";
				$res = $db->execute($sql);

				// send_table pause_ch
				$sql = "UPDATE send_table SET pause_ch = $need_num WHERE id = '{$id}'";
				$res = $db->execute($sql);
			}else{
				// 数量小于等于日本，消耗日本库存
				$sql = "UPDATE goods_type SET b_repo = b_repo - $goods_num WHERE goods_code = '{$goods_code}'";
				$res = $rdb->execute($sql);

				// info pause_jp
				$sql = "UPDATE $response_info SET pause_jp = $goods_num WHERE id = '{$info_id}'";
				$res = $db->execute($sql);

				// send_table pause_jp
				$sql = "UPDATE send_table SET pause_jp = $goods_num WHERE id = '{$id}'";
				$$res = $db->execute($sql);
			}

			// 更新INFO
			$sql = "UPDATE $response_info SET $list_field = '{$new_key}' WHERE id = '{$info_id}'";
			$res = $db->execute($sql);

			// 更新 send_table
			$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
			$res = $db->execute($sql);
		}
		
	}
	if($field_name == 'due_money'){
		$ch_field = '代引金额';
		$list_field = 'pay_money';
		// 更新LIST
		// $sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		// $res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}

	// 日志
	$do = '修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

	echo 'ok';
}

// 邮编、地址验证。通过则更新。
if(isset($_GET['change_post_addr'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$id = $_GET['id'];	//send_table 的 ID
	$oms_id = $_GET['oms_id'];
	$info_id = $_GET['info_id'];
	$response_list = $station.'_response_list';
	$new_post_code = addslashes($_GET['new_post_code']);
	$new_address = addslashes($_GET['new_address']);

	// 查询出该 post_code 的对应市区
	$sql = "SELECT post_name FROM oms_post WHERE post_code = '{$new_post_code}'";
	$res = $db->getOne($sql);
	$post_name = $res['post_name'];

	// 去掉括弧里面的地址
	if(strstr($post_name,'（')==true){
		$res = explode('（',$post_name);
		$post_name = $res[0];
	}

	if($post_name == ''){
		echo '邮编不存在。';
	}else{
		// 邮编和地址匹配
		if(strstr($new_address, $post_name) == true){
			// 查询原字段值
			$sql = "SELECT post_code,address FROM $response_list WHERE id = '{$oms_id}'";
			$res = $db->getOne($sql);
			$o_post_code = $res['post_code'];
			$o_address = $res['address'];

			// 保存 LIST
			$sql = "UPDATE $response_list SET post_code = '{$new_post_code}',address = '{$new_address}' WHERE id = '{$oms_id}'";
		    $res = $db->execute($sql);

		    // 保存 send_table
			$sql = "UPDATE send_table SET who_post = '{$new_post_code}',who_house = '{$new_address}' WHERE id = '{$id}'";
		    $res = $db->execute($sql);

		    // 日志
			$do = 'OMS-ID【'.$oms_id.'】修改 <邮编/地址>【'.$o_post_code.'/'.$o_address.'】为【'.$new_post_code.'/'.$new_address.'】';
			oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

			echo 'ok';
		}else{
			echo '邮编、地址不匹配。';
		}
	}
}

// 计算发货单
if(isset($_GET['repo_status'])){
	$sql = "SELECT station,send_id FROM send_table";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$station = $value['station'];
		$response_list = $station.'_response_list';
		$send_id = $value['send_id'];
		$sql = "SELECT sum(pause_ch) AS sum_ch,sum(pause_jp) AS sum_jp FROM send_table WHERE send_id = '{$send_id}'";
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
		// 更新发货单
		$sql = "UPDATE send_table SET repo_status = '{$repo_status}' WHERE send_id = '{$send_id}'";
		$res = $db->execute($sql);

		// 同步回LIST
		$sql = "UPDATE $response_list list,send_table st SET list.repo_status = st.repo_status WHERE list.id = st.oms_id";
		$res = $db->execute($sql);
	}	
}