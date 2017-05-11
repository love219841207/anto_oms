<?php
require_once("../header.php");

//修改密码
if(isset($_GET['change_pwd'])){
	$old_pwd = addslashes($_GET['change_pwd']);
	$new_pwd = addslashes($_GET['new_pwd']);
	$re_new_pwd = addslashes($_GET['re_new_pwd']);
	if($new_pwd!==$re_new_pwd){
		echo "error_re";
		return false;
	}
	if($new_pwd == $old_pwd){
		echo "no_change";
		return false;
	}
	$user = $_SESSION['oms_u_num'];
	$sql = "SELECT * FROM user_oms WHERE u_num = $user AND u_pwd='{$old_pwd}'";
    $res = $db->getOne($sql);

    if(empty($res)){
        echo "error_old";
    }else{
    	session_destroy();
    	//修改
    	$sql = "UPDATE user_oms SET u_pwd='{$new_pwd}' WHERE u_num = $user";
    	$res = $db->execute($sql);
    	echo "ok";
    }
}
