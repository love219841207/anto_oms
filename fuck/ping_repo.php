<?php
require_once("./header.php");

// ping_repo
if(isset($_GET['ping_repo'])){
	$sql = "SELECT id as repo_status FROM sys_status";
	$res = $rdb->getOne($sql);
	echo json_encode($res);
}