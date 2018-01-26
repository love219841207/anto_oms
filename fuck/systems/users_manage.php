<?php
require_once("../header.php");

//查询员工列表
if(isset($_GET['get_user_list'])){
	$sql = "SELECT * FROM user_oms ORDER BY id DESC";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

//新增员工
if(isset($_GET['add_user'])){
	$add_user = addslashes($_GET['add_user']);
	$sql = "SELECT * FROM user_oms WHERE u_name = '{$add_user}'";
    $res = $db->getOne($sql);
    
	if(empty($res)){
		//获取最大工号
	    $sql = "SELECT Max(u_num) as max_num FROM user_oms";
	    $res = $db->getOne($sql);
	    $new_num = $res['max_num']+1;
		$sql = "INSERT INTO user_oms (u_num,u_name,u_pwd,u_side_bar,u_amazon,u_rakuten,u_yahoo,u_p_yahoo) VALUES ('{$new_num}','{$add_user}','123456','1','-','-','-','-')";
		$res = $db->execute($sql);
		echo 'ok';
	}else{
		echo 'has';
	}
}

//员工密码重置
if(isset($_GET['re_pwd'])){
	$re_num = $_GET['re_pwd'];
	$sql = "UPDATE user_oms SET u_pwd = '123456' WHERE u_num = '{$re_num}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//删除员工
if(isset($_GET['del_user'])){
	$del_num = $_GET['del_user'];
	$sql = "DELETE FROM user_oms WHERE u_num = '{$del_num}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//员工店铺分配获取
if(isset($_GET['get_store'])){
	$u_num = $_GET['get_store'];
	$station = $_GET['station'];
	$u_station = 'u_'.strtolower($station);

	//获取该员工在该平台下的店铺
	$sql = "SELECT $u_station as my_store FROM user_oms WHERE u_num = '{$u_num}'";
    $res = $db->getOne($sql);
    $my_store = explode(',',$res['my_store']); 

	// 获取该平台下的所有店铺
	$sql = "SELECT * FROM oms_store WHERE station = '{$station}'";
    $res = $db->getAll($sql);
    foreach ($res as $key => $value) {
    	//判断是否已经包含
    	if(in_array($value['store_name'], $my_store)){
    		$res[$key]['has'] = true;
    	}else{
    		$res[$key]['has'] = false;
    	}
    }
    //返回带包含的结果集
    echo json_encode($res);
}

//保存员工店铺
if(isset($_POST['cg_store_conf'])){
	$cg_store_conf = $_POST['cg_store_conf'];
	if($cg_store_conf == null){
		$cg_store_conf = '-';
	}
	$cg_u_num = $_POST['cg_u_num'];
	$cg_station = $_POST['cg_station'];
	$u_station = 'u_'.strtolower($cg_station);
	$sql = "UPDATE user_oms SET $u_station = '{$cg_store_conf}' WHERE u_num = '{$cg_u_num}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 获取 mail_name
if(isset($_GET['get_mail_name'])){
    $station = $_GET['station'];
    $conf = 'conf_'.$station;
    $store_name = $_GET['get_mail_name'];
    $sql = "SELECT mail_name FROM $conf WHERE store_name = '{$store_name}'";
    $res = $db->getOne($sql);
    echo $res['mail_name'];
}

//topbar 获取员工现有店铺
if(isset($_GET['get_my_store'])){
	$station = $_GET['get_my_store'];
	$u_station = 'u_'.strtolower($station);

	//获取该员工在该平台下的店铺
	$sql = "SELECT $u_station as my_store FROM user_oms WHERE u_num = '{$_SESSION['oms_u_num']}'";
    $res = $db->getOne($sql);

    if($res['my_store'] == '*'){
    	//如果是*，则为全部店铺
    	$sql = "SELECT store_name FROM oms_store WHERE station = '{$station}'";
    	$res = $db->getAll($sql);
    	$arr = array();
    	foreach ($res as $value) {
    		array_push($arr, $value['store_name']);
    	}
    	echo json_encode($arr);
    }else{
	    $my_store = explode(',',$res['my_store']); 
	    echo json_encode($my_store);
    }
}

// 是否可发货操作
if(isset($_GET['can_send'])){
	$u_num = $_GET['can_send'];

	$sql = "UPDATE user_oms SET can_send = (CASE can_send WHEN  '0' THEN '1' WHEN '1' THEN '0' END) WHERE u_num = '{$u_num}'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 是否可售后操作
if(isset($_GET['can_repair'])){
    $u_num = $_GET['can_repair'];

    $sql = "UPDATE user_oms SET can_repair = (CASE can_repair WHEN  '0' THEN '1' WHEN '1' THEN '0' END) WHERE u_num = '{$u_num}'";
    $res = $db->execute($sql);
    echo 'ok';
}