<?php
require_once("../header.php");

//更新page_size
if(isset($_GET['update_pagesize'])){
	$page_size = $_GET['update_pagesize'];
	$sql = "UPDATE user_oms SET page_size = '{$page_size}' WHERE u_num = '{$_SESSION['oms_u_num']}'";
	$res = $db->execute($sql);
	echo 'ok';
}