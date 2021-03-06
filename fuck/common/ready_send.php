<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
// 退库
if(isset($_GET['to_back_repo'])){
	$send_id = $_GET['to_back_repo'];
	//查询是否存在此合单发货ID
	$sql = "SELECT * FROM send_table WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);

	if(empty($res)){
		echo '没有此合单ID';
	}else{
		// 如果存在，则退还该合单下的所有扣押的日本+中国库存
	 	$sql = "SELECT station,oms_id,order_id,goods_code,pause_ch,pause_jp FROM send_table WHERE send_id = '{$send_id}'";
	 	$res = $db->getAll($sql);

	 	$response_list = '';
	 	$response_info = '';

	 	foreach ($res as $val) {
	 		$station = $val['station'];
	 		$oms_id = $val['oms_id'];
	 		$order_id = $val['order_id'];
	 		$goods_code = $val['goods_code'];
	 		$pause_ch = $val['pause_ch'];
	 		$pause_jp = $val['pause_jp'];

	 		$response_list = $station."_response_list";
	 		$response_info = $station."_response_info";

	 		// 还库存
			$sql = "UPDATE goods_type SET a_repo = a_repo + $pause_ch,b_repo = b_repo + $pause_jp WHERE goods_code = '{$goods_code}'";
			$res = $rdb->execute($sql);

			// 修改 info 合单下的订单扣押数为0
			$sql = "UPDATE $response_info SET is_pause = '',pause_ch = '0',pause_jp = '0' WHERE order_id = '{$order_id}'";
			$res = $db->execute($sql);

			//查询店铺
			$sql = "SELECT store FROM $response_list WHERE send_id = '{$send_id}'";
			$res = $db->getOne($sql);
			$store = $res['store'];

			// 日志
			$do = '退库 <'.$goods_code.'> CH【'.$pause_ch.'】JP【'.$pause_jp.'】';
			oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	 	}

		// 修改 list 合单下的订单状态order_line = '-5'
		$sql = "UPDATE $response_list SET order_line = '-5' WHERE send_id = '{$send_id}'";
		$res = $db->execute($sql);

		// 删除 readsend 中该合单下的订单
		$sql = "DELETE FROM send_table WHERE send_id = '{$send_id}'";
		$res = $db->execute($sql);

		echo 'ok';
	}
}

// 转入待回单
if(isset($_GET['to_wait'])){
	$sql = "UPDATE send_table SET has_pack = '1' WHERE has_pack = '0'";
    $res = $db->execute($sql);
    echo 'ok';
}

// 待回单转待出单
if(isset($_GET['back_to_ready'])){
	$id = $_GET['back_to_ready'];

	$sql = "UPDATE send_table SET has_pack = '0',table_status = '0' WHERE id = '{$id}'";
	$res = $db->execute($sql);

	echo 'ok';
}

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
	reset_express();
	echo 'ok';
}

// 删除bag
if(isset($_GET['del_bag'])){
	$del_bag = $_GET['del_bag'];
	$sql = "DELETE FROM send_table WHERE id = '{$del_bag}'";
    $res = $db->execute($sql);
    echo 'ok';
}

//重置功能
function reset_express(){
	$db = new PdoMySQL();
	// 重置快递公司 和包裹
	$sql = "UPDATE send_table SET express_company ='',pack_id = '' WHERE has_pack = '0'";
	$res = $db->execute($sql);

	// 重置配送方式
	$sql = "UPDATE send_table SET send_method = '宅配便' WHERE send_method = '宅急便' AND has_pack = '0'";
	$res = $db->execute($sql);

	// item_line
	$sql = "UPDATE send_table SET item_line = '0' WHERE has_pack = '0'";
	$res = $db->execute($sql);
}


// 亚马逊mail
function amz_mail_own_key(){
	$db = new PdoMySQL();
	// 亚马逊默认宅配便
	$sql = "UPDATE send_table SET send_method = '宅配便' WHERE station = 'amazon' AND has_pack = '0'";
	$res = $db->execute($sql);

	// 检索amz_mail库的 DM便
	$sql = "SELECT goods_code FROM amz_mail WHERE mail_type = 'DM便' ORDER BY ID";
	$res = $db->getAll($sql);
	$amz_mail = array();
	foreach ($res as $value) {
		array_push($amz_mail, "'".$value['goods_code']."'");
	}
	$amz_mail = implode(',', $amz_mail);

	// 改亚马逊 mail便 的为DM便
	$sql = "UPDATE send_table SET send_method = 'DM便',express_company = 'ヤマト運輸' WHERE goods_code in ($amz_mail) AND station = 'amazon' AND has_pack = '0'";
	$res = $db->execute($sql);

	// 检索amz_mail库的 乃口pos
	$sql = "SELECT goods_code FROM amz_mail WHERE mail_type = 'ネコポス' ORDER BY ID";
	$res = $db->getAll($sql);
	$amz_mail1 = array();
	foreach ($res as $value) {
		array_push($amz_mail1, "'".$value['goods_code']."'");
	}
	$amz_mail1 = implode(',', $amz_mail1);

	// 改mail便 
	$sql = "UPDATE send_table SET send_method = 'ネコポス',express_company = 'ヤマト運輸' WHERE goods_code in ($amz_mail1) AND station = 'amazon' AND has_pack = '0'";
	$res = $db->execute($sql);
}

function make_bags(){
	$db = new PdoMySQL();

	// 同捆中存在DM便和宅配则改为宅配
	$sql = "SELECT pack_id,count(1) as cct FROM send_table WHERE has_pack = '0' GROUP BY pack_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$cct = $val['cct'];
		$pid = $val['pack_id'];

		if($cct > 1){	//如果订单数大于1
			// echo $pid.' ';
			$sql = "SELECT count(1) as zpb FROM send_table WHERE send_method = '宅配便' AND pack_id = '{$pid}'";
			$res = $db->getOne($sql);
			$zpb = $res['zpb'];
			if($zpb > 0){	//如果至少有一单宅配
				$sql = "SELECT count(1) as dmb FROM send_table WHERE send_method = 'DM便' AND pack_id = '{$pid}'";
				$res = $db->getOne($sql);
				$dmb = $res['dmb'];
				if($dmb > 0){	//如果至少有一单DM便
					$sql = "UPDATE send_table SET send_method = '宅配便',express_company = '佐川急便' WHERE pack_id = '{$pid}'";
					$res = $db->execute($sql);
				}
			}	
		}
	}

	// 同捆中存在ネコポス便和宅配则改为宅配
	$sql = "SELECT pack_id,count(1) as cct FROM send_table WHERE has_pack = '0' GROUP BY pack_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$cct = $val['cct'];
		$pid = $val['pack_id'];

		if($cct > 1){	//如果订单数大于1
			// echo $pid.' ';
			$sql = "SELECT count(1) as zpb FROM send_table WHERE send_method = '宅配便' AND pack_id = '{$pid}'";
			$res = $db->getOne($sql);
			$zpb = $res['zpb'];
			if($zpb > 0){	//如果至少有一单宅配
				$sql = "SELECT count(1) as dmb FROM send_table WHERE send_method = 'ネコポス' AND pack_id = '{$pid}'";
				$res = $db->getOne($sql);
				$dmb = $res['dmb'];
				if($dmb > 0){	//如果至少有一单ネコポス
					$sql = "UPDATE send_table SET send_method = '宅配便',express_company = '佐川急便' WHERE pack_id = '{$pid}'";
					$res = $db->execute($sql);
				}
			}	
		}
	}

	// 这里判断 一单包括 DM便 和 乃口pos 的情况
	$sql = "SELECT pack_id,count(1) as cct FROM send_table WHERE has_pack = '0' GROUP BY pack_id";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$cct = $val['cct'];
		$pid = $val['pack_id'];

		if($cct > 1){	//如果订单数大于1
			// echo $pid.' ';
			$sql = "SELECT count(1) as pos FROM send_table WHERE send_method = 'ネコポス' AND pack_id = '{$pid}'";
			$res = $db->getOne($sql);
			$pos = $res['pos'];
			if($pos > 0){	//如果至少有一单乃口pos
				$sql = "SELECT count(1) as dmb FROM send_table WHERE send_method = 'DM便' AND pack_id = '{$pid}'";
				$res = $db->getOne($sql);
				$dmb = $res['dmb'];
				if($dmb > 0){	//如果至少有一单DM便
					$sql = "UPDATE send_table SET send_method = 'ネコポス',express_company = 'ヤマト運輸' WHERE pack_id = '{$pid}'";
					$res = $db->execute($sql);
				}
			}	
		}
	}

	// 删除自动添加的包裹
	$sql = "DELETE FROM send_table WHERE other_1 = 'add' AND has_pack = '0'";
	$res = $db->execute($sql);

	// 平台体积运算 DM便
	$sql = "SELECT pack_id FROM send_table WHERE has_pack = '0' AND send_method = 'DM便' GROUP BY pack_id";
	$res = $db->getAll($sql);

	foreach ($res as $value) {
		$pack_id = $value['pack_id'];

		// 基础数据
		$sql = "SELECT send_id,who_name,who_tel,who_post,repo_status,import_day,store_name,station,express_company,send_method,who_house FROM send_table WHERE pack_id = '{$pack_id}'";
		$res = $db->getOne($sql);
		
		$send_id = $res['send_id'];
		$who_house = addslashes($res['who_house']);
		$who_tel = $res['who_tel'];
		$who_post = $res['who_post'];
		$express_company = $res['express_company'];
		$send_method = $res['send_method'];
		$store_name = $res['store_name'];
		$station = $res['station'];
		$who_name = $res['who_name'];
		$import_day = $res['import_day'];
		$repo_status = $res['repo_status'];

		//查询体积
		$sql = "SELECT goods_code,out_num FROM send_table WHERE pack_id = '{$pack_id}'";
		$res = $db->getAll($sql);
		$sum_own_key = '0';
		foreach ($res as $value) {
			$now_goods_code = $value['goods_code'];
			$now_out_num = $value['out_num'];
			$sql = "SELECT own_key FROM amz_mail WHERE goods_code = '{$now_goods_code}'";
			$ress = $db->getOne($sql);
			$now_key = $now_out_num * $ress['own_key'];
			$sum_own_key = $sum_own_key + $now_key;
		}

		if(1201 >$sum_own_key AND $sum_own_key > 600){	
			$ppc = $pack_id.'(1/2)';
			$sql = "UPDATE send_table SET pack_id = '{$ppc}' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
			// 增加一个包裹
			$p_a = $pack_id.'(2/2)';
			$sql = "INSERT INTO send_table (goods_code,who_name,send_id,pack_id,pack_count,repo_status,import_day,oms_id,info_id,order_id,store_name,station,express_company,send_method,who_house,who_tel,who_post,other_1)VALUES('bag (2/2)','{$who_name}',concat('{$send_id}','-2'),'{$p_a}','{$p_a}','{$repo_status}','{$import_day}',0,0,0,'{$store_name}','{$station}','{$express_company}','{$send_method}','{$who_house}','{$who_tel}','{$who_post}','add')";
			$res = $db->execute($sql);
		}elseif (1801 > $sum_own_key AND $sum_own_key > 1200) {		
			$ppc = $pack_id.'(1/3)';
			$sql = "UPDATE send_table SET pack_id = '{$ppc}' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
			// 增加两个包裹
			$p_a = $pack_id.'(2/3)';
			$p_b = $pack_id.'(3/3)';
			$sql = "INSERT INTO send_table (goods_code,who_name,send_id,pack_id,pack_count,repo_status,import_day,oms_id,info_id,order_id,store_name,station,express_company,send_method,who_house,who_tel,who_post,other_1)VALUES('bag (2/3)','{$who_name}',concat('{$send_id}','-2'),'{$p_a}','{$p_a}','{$repo_status}','{$import_day}',0,0,0,'{$store_name}','{$station}','{$express_company}','{$send_method}','{$who_house}','{$who_tel}','{$who_post}','add')";
			$res = $db->execute($sql);
			$sql = "INSERT INTO send_table (goods_code,who_name,send_id,pack_id,pack_count,repo_status,import_day,oms_id,info_id,order_id,store_name,station,express_company,send_method,who_house,who_tel,who_post,other_1)VALUES('bag (3/3)','{$who_name}',concat('{$send_id}','-3'),'{$p_b}','{$p_b}','{$repo_status}','{$import_day}',0,0,0,'{$store_name}','{$station}','{$express_company}','{$send_method}','{$who_house}','{$who_tel}','{$who_post}','add')";
			$res = $db->execute($sql);
		}elseif (1800 < $sum_own_key){
			// 转宅配
			$sql = "UPDATE send_table SET express_company = '佐川急便',send_method = '宅配便' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
		}else{
			// 如果小于 600，转回原型
			$sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = 'DM便' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
		}
	}

	// 平台体积运算 ネコポス
	$sql = "SELECT pack_id FROM send_table WHERE has_pack = '0' AND send_method = 'ネコポス' GROUP BY pack_id";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$pack_id = $value['pack_id'];

		// 基础数据
		$sql = "SELECT send_id,who_name,who_tel,who_post,repo_status,import_day,store_name,station,express_company,send_method,who_house FROM send_table WHERE pack_id = '{$pack_id}'";
		$res = $db->getOne($sql);
		
		$send_id = $res['send_id'];
		$who_house = $res['who_house'];
		$who_tel = $res['who_tel'];
		$who_post = $res['who_post'];
		$express_company = $res['express_company'];
		$send_method = $res['send_method'];
		$store_name = $res['store_name'];
		$station = $res['station'];
		$who_name = $res['who_name'];
		$import_day = $res['import_day'];
		$repo_status = $res['repo_status'];

		//查询体积
		$sql = "SELECT goods_code,out_num FROM send_table WHERE pack_id = '{$pack_id}'";
		$res = $db->getAll($sql);
		$sum_own_key = '0';
		foreach ($res as $value) {
			$now_goods_code = $value['goods_code'];
			$now_out_num = $value['out_num'];
			$sql = "SELECT own_key FROM amz_mail WHERE goods_code = '{$now_goods_code}'";
			$ress = $db->getOne($sql);
			$now_key = $now_out_num * $ress['own_key'];
			$sum_own_key = $sum_own_key + $now_key;
		}

		if(1201 >$sum_own_key AND $sum_own_key > 600){	
			$ppc = $pack_id.'(1/2)';
			$sql = "UPDATE send_table SET pack_id = '{$ppc}' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
			// 增加一个包裹
			$p_a = $pack_id.'(2/2)';
			$sql = "INSERT INTO send_table (goods_code,who_name,send_id,pack_id,pack_count,repo_status,import_day,oms_id,info_id,order_id,store_name,station,express_company,send_method,who_house,who_tel,who_post,other_1)VALUES('bag (2/2)','{$who_name}',concat('{$send_id}','-2'),'{$p_a}','{$p_a}','{$repo_status}','{$import_day}',0,0,0,'{$store_name}','{$station}','{$express_company}','{$send_method}','{$who_house}','{$who_tel}','{$who_post}','add')";
			$res = $db->execute($sql);
		}elseif (1200 < $sum_own_key){
			// 转宅配
			$sql = "UPDATE send_table SET express_company = '',send_method = '宅配便' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
		}else{
			// 如果小于 600，转回原型
			$sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = 'ネコポス' WHERE pack_id = '{$pack_id}'";
			$res = $db->execute($sql);
		}
	}

}

// 打包
if(isset($_GET['packing'])){
	// 代引金额过滤
	$sql = "UPDATE send_table SET due_money = 0 WHERE is_cod <> 'COD'";
	$res = $db->execute($sql);

	reset_express();	// 重置

	// 亚马逊分配
	amz_mail_own_key();

	// pack_id
	$sql = "UPDATE send_table SET pack_id = oms_id,pack_count = '' WHERE has_pack = '0'";
	$res = $db->execute($sql);

	// 如果是合单的 pack_id
	$sql = "SELECT send_id FROM send_table WHERE has_pack = '0' AND item_line = '0' AND send_id LIKE 'H%' GROUP BY send_id";
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
	$sql = "UPDATE send_table SET pack_id = concat('A',pack_id) WHERE station = 'amazon' AND has_pack = '0'";
	$res = $db->execute($sql);
	$sql = "UPDATE send_table SET pack_id = concat('S',pack_id) WHERE station = 'yahoo' AND has_pack = '0'";
	$res = $db->execute($sql);
	$sql = "UPDATE send_table SET pack_id = concat('R',pack_id) WHERE station = 'rakuten' AND has_pack = '0'";
	$res = $db->execute($sql);
	$sql = "UPDATE send_table SET pack_id = concat('P',pack_id) WHERE station = 'p_yahoo' AND has_pack = '0'";
	$res = $db->execute($sql);

	// 先变成佐川
	$sql = "UPDATE send_table SET express_company = '佐川急便' WHERE express_company = '' or express_company = '无' AND has_pack = '0'";
	$res = $db->execute($sql);

	// 如果是乐天,客人指定的配送方式
	// $sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = 'DM便' WHERE send_method = 'メール便' AND station = 'rakuten'";
	// $res = $db->execute($sql);

	// 其他平台，根据运费代码进行配送方式分配，同步运费代码

	// 乐天
	$sql = "UPDATE send_table send,rakuten_response_info info SET send.yfcode = info.yfcode WHERE send.info_id = info.id AND send.station = 'rakuten'";
	$res = $db->execute($sql);

	// 拍卖
	$sql = "UPDATE send_table send,p_yahoo_response_info info SET send.yfcode = info.yfcode WHERE send.info_id = info.id AND send.station = 'p_yahoo'";
	$res = $db->execute($sql);

	// 雅虎
	$sql = "UPDATE send_table send,yahoo_response_info info SET send.yfcode = info.yfcode WHERE send.info_id = info.id AND send.station = 'yahoo'";
	$res = $db->execute($sql);

	// 运费代码配送方式
	$sql = "UPDATE send_table send,yf_code yf SET send.send_method = yf.send_method WHERE send.yfcode = yf.yf_code_name AND has_pack = '0'";
	$res = $db->execute($sql);

	// 所有的mail发黑猫
	$sql = "UPDATE send_table SET express_company = 'ヤマト運輸' WHERE  send_method in ('DM便','ネコポス') AND has_pack = '0'";
	$res = $db->execute($sql);

	// 分包裹
	make_bags();
	
	// 地址分配配送公司，更新黑猫地址	（神奈川県，埼玉県，茨城県，群馬県，山梨県）
	$sql = "UPDATE send_table SET express_company = 'ヤマト運輸',send_method = '宅急便' WHERE has_pack = '0' AND send_method = '宅配便' AND repo_status = '日' AND (who_house LIKE '%神奈川県%' OR who_house LIKE '%埼玉県%' OR who_house LIKE '%茨城県%' OR who_house LIKE '%群馬県%' OR who_house LIKE '%山梨県%')";
	$res = $db->execute($sql);

	// item_line
	$sql = "UPDATE send_table SET item_line = '1'";
	$res = $db->execute($sql);

	// 更新单号和快递日期到list表 三个平台次更新 
	$sql = "UPDATE amazon_response_list list,send_table send SET list.express_company = send.express_company,list.send_method = send.send_method WHERE list.order_id = send.order_id";
	$res = $db->execute($sql);

	echo 'ok';
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