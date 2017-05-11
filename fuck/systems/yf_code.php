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
	$sql = "SELECT count(1) as has_num FROM yf_code WHERE yf_code_name = '{$yf_code_name}'";
	$res = $db->getOne($sql);

	if($res['has_num']== 0){
		$sql = "INSERT INTO yf_code (yf_code_name,level,send_method,need_cod,default_yf,default_one_yf) VALUES ('{$yf_code_name}','{$level}','{$send_method}','{$need_cod}','{$default_yf}','{$default_one_yf}')";
		$res = $db->execute($sql);
		echo 'ok';
	}else{
		echo '已存在此代码！';
	}
}

// 修改运费代码状态
if(isset($_GET['change_status'])){
	$id = $_GET['change_status'];
	$field = $_GET['field'];

	$sql = "UPDATE yf_code SET $field = (CASE $field WHEN  '0' THEN '1' WHEN '1' THEN '0' END) WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}