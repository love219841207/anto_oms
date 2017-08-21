<?php
require_once("header.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入日本邮编单
if(isset($_GET['import_file'])){

	//清空邮编表
    $sql = "TRUNCATE oms_post;";
    $res = $db->execute($sql);

	$sql = "LOAD DATA INFILE '/Users/ycmbcd/工程/anto_oms/uploads/post.csv'	INTO TABLE oms_post fields terminated by '\,' optionally enclosed by '\"' lines terminated by '\n' ignore 1 lines;";
	// $sql = "LOAD DATA INFILE '/opt/anto_oms_data/post.csv'	INTO TABLE oms_post fields terminated by '\,' optionally enclosed by '\"' lines terminated by '\n' ignore 1 lines;";
	$res = $db->execute($sql);

	look_post();
}

//检测邮编
function look_post(){
	$db = new PdoMySQL();
	//查询导入总数
	$sql = "SELECT count(1) as insert_num FROM oms_post";
	$res = $db->getOne($sql);
	$insert_num = $res['insert_num'];

	//检测查询
	$sql = "SELECT * FROM oms_post ORDER BY id DESC LIMIT 0,4";
	$res = $db->getAll($sql);
	$look_data1 = $res;

	$sql = "SELECT * FROM oms_post ORDER BY id LIMIT 0,1";
	$res = $db->getAll($sql);
	$look_data2 = $res;

	//final_res
	$final_res['status'] = 'ok';
	$final_res['insert_num'] = $insert_num;
	$final_res['look_data1'] = $look_data1;
	$final_res['look_data2'] = $look_data2;
	echo json_encode($final_res);
}

if(isset($_GET['look_post'])){
	look_post();
}

// 邮编替换
if(isset($_GET['replace_post'])){
	$from_post = addslashes($_GET['replace_post']);
	@$to_post = addslashes($_GET['to_post']);
	$sql = "UPDATE oms_post SET post_name = REPLACE( post_name,'{$from_post}','{$to_post}' )";
	$db->execute($sql);
	echo 'ok';
}