<?php
require_once("header.php");

//获取版本号
if(isset($_GET['get_version'])){
	$get_version = $_GET['get_version'];
	$sql = "SELECT sys_version FROM sys_version WHERE id = '1'";
    $res = $db->getOne($sql);
	echo $res['sys_version'];
}

//版本
if(isset($_GET['updte_version'])){
	$updte_version = $_GET['updte_version'];
	$sql = "UPDATE sys_version SET sys_version = '{$updte_version}' WHERE id = '1'";
    $res = $db->execute($sql);
	echo 'ok';
}