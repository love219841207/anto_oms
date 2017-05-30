<?php
require_once("./header.php");
require_once("../pdo/repair.PdoMySQL.class.php");//REPAIR_PDO

// ping_repo
if(isset($_GET['ping_repair'])){
	$shdb = new RepairPdoMySQL();
	$sql = "SELECT id FROM user where u_name = 'ycmbcd'";
	$res = $shdb->getOne($sql);
	echo $res['id'];
}