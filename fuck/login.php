<?php
//开启session
session_start();

$dir = dirname(__FILE__);
require_once($dir."/../pdo/PdoMySQL.class.php");//PDO
require_once($dir."/./log.php");
$db = new PdoMySQL();

//登录验证
if(isset($_POST['u_num'])){
	$u_num = $_POST['u_num'];
	$u_pwd = $_POST['u_pwd'];
	$u_num = addslashes($u_num);   //防止SQL注入
	$u_pwd = addslashes($u_pwd);   
	$sql = "SELECT * FROM user_oms WHERE u_num='{$u_num}' AND u_pwd='{$u_pwd}'";
    $res = $db->getOne($sql);

    if(empty($res)){
        echo "0";
    }else{
		$_SESSION['oms_u_num'] = $u_num;
		$_SESSION['oms_u_name'] = $res['u_name'];

		//日志
		$do = '[Login] 登入系统';
		oms_log($_SESSION['oms_u_name'],$do,'system');

    	echo "go";
    }
}

//输出u_name
if(isset($_GET['u_name'])){
	@$u_name = $_SESSION['oms_u_name'];
	if($u_name==''){
		echo 'logout';die;
	}
	echo $_SESSION['oms_u_name'];
}

//输出u_num
if(isset($_GET['u_num'])){
	@$u_num = $_SESSION['oms_u_num'];
	if($u_num==''){
		echo 'logout';die;
	}
	echo $_SESSION['oms_u_num'];
}

// 获取side_bar状态
if(isset($_GET['side_bar_status'])){
	@$u_num = $_SESSION['oms_u_num'];
	$sql = "SELECT u_side_bar FROM user_oms WHERE u_num = '{$u_num}'";
    $res = $db->getOne($sql);
	echo $res['u_side_bar'];
}

// 更改side_bar状态
if(isset($_GET['update_side_bar_status'])){
	@$u_num = $_SESSION['oms_u_num'];
	$sql = "UPDATE user_oms SET u_side_bar = (CASE u_side_bar WHEN  '1' THEN '2' ELSE '1' END) WHERE u_num = '{$u_num}'";
    $res = $db->getOne($sql);
	echo 'ok';
}

//修改密码
if(isset($_POST['change_pwd'])){
	$old_pwd = $_POST['change_pwd'];
	$new_pwd = $_POST['new_pwd'];
	$re_pwd = $_POST['re_pwd'];
	$old_pwd = addslashes($old_pwd);   //防止SQL注入
	$new_pwd = addslashes($new_pwd);   
	$re_pwd = addslashes($re_pwd);   
	if($new_pwd!==$re_pwd){
		echo "error";
		return false;
	}
	$user = $_SESSION['oms_u_num'];
	$sql = "SELECT * FROM user_oms WHERE u_num = $user AND u_pwd='{$old_pwd}'";
    $res = $db->getOne($sql);

    if(empty($res)){
        echo "0";
    }else{
    	//修改
    	$sql = "UPDATE user_oms SET u_pwd='{$new_pwd}' WHERE u_num = $user";
    	$res = $db->execute($sql);
    	echo "ok";
    }
}

//退出
if(isset($_GET['logout'])){
	session_destroy();

	//日志
	$do = '[Logout] 登出系统';
	oms_log($_SESSION['oms_u_name'],$do,'system');

	echo "bye";
}
?>