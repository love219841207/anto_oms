<?php
require_once("../header.php");
// 查询运费表
if(isset($_GET['get_table'])){
	$sql = "SELECT * FROM yf_code ORDER BY id DESC";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 添加运费代码
if(isset($_GET['add_yfcode'])){
	$yf_code_name = $_GET['add_yfcode'];
	$level = $_GET['level'];
	$send_method = $_GET['send_method'];
	$need_cod = $_GET['need_cod'];
	$default_yf = $_GET['default_yf'];
	$default_one_yf = $_GET['default_one_yf'];

	// 查询是否存在此运费代码
	// $sql = "SELECT count(1) as has_num FROM yf_code WHERE yf_code_name = '{$yf_code_name}'";
	// $res = $db->getOne($sql);

	// if($res['has_num']== 0){
		$sql = "INSERT INTO yf_code (yf_code_name,level,send_method,need_cod,default_yf,default_one_yf) VALUES ('{$yf_code_name}','{$level}','{$send_method}','{$need_cod}','{$default_yf}','{$default_one_yf}')";
		$res = $db->execute($sql);
		echo 'ok';
	// }else{
	// 	echo '已存在此代码！';
	// }
}

// 修改运费代码状态
if(isset($_GET['change_status'])){
	$id = $_GET['change_status'];
	$field = $_GET['field'];

	$sql = "UPDATE yf_code SET $field = (CASE $field WHEN  '0' THEN '1' WHEN '1' THEN '0' END) WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 删除运费代码
if(isset($_GET['del_yfcode'])){
	$id = $_GET['del_yfcode'];

	$sql = "DELETE FROM yf_code WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 获取地区
if(isset($_GET['get_area'])){
	$yf_code = $_GET['get_area'];

	$sql = "SELECT * FROM jp_area WHERE area NOT IN (SELECT area FROM yf_money WHERE yf_code = '{$yf_code}')";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 获取特殊运费代码列表
if(isset($_GET['get_spe_list'])){
	$yf_code = $_GET['get_spe_list'];

	$sql = "SELECT * FROM yf_money WHERE yf_code = '{$yf_code}' ORDER BY id DESC";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 删除特殊运费代码
if(isset($_GET['del_spe'])){
	$id = $_GET['del_spe'];

	$sql = "DELETE FROM yf_money WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 特殊地区运费代码
if(isset($_GET['add_spe_yfcode'])){
	$yf_code = $_GET['add_spe_yfcode'];
	$add_area = $_GET['add_area'];
	$spe_yf = $_GET['spe_yf'];
	$spe_one_yf = $_GET['spe_one_yf'];
	$sql = "INSERT INTO yf_money (yf_code,area,yf_money,yf_add) VALUES ('{$yf_code}','{$add_area}','{$spe_yf}','{$spe_one_yf}')";
	$res = $db->execute($sql);
	echo 'ok';
}


// 运费代码参数修改
if(isset($_GET['save_cg_yfcode'])){
	$save_cg_yfcode = $_GET['save_cg_yfcode'];
	$new_send_method = $_GET['new_send_method'];
	$new_level = $_GET['new_level'];
	$new_default_yf = $_GET['new_default_yf'];
	$new_default_one_yf = $_GET['new_default_one_yf'];
	$sql = "UPDATE yf_code SET level = '{$new_level}',send_method = '{$new_send_method}',default_yf = '{$new_default_yf}',default_one_yf = '{$new_default_one_yf}' WHERE yf_code_name = '{$save_cg_yfcode}'";
	$res = $db->execute($sql);
	echo 'ok';
}
