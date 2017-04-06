<?php
require_once("../header.php");

if(isset($_GET['log_type'])){
	$log_type = $_GET['log_type'];
	$log_s_date = $_GET['log_s_date'];
	$log_e_date = $_GET['log_e_date'];
	$log_user = $_GET['log_user'];
	$log_page = $_GET['log_page'];
	$log_page = $log_page * 100;

	if($log_type == 'all'){
		$log_type = '';
	}
	if($log_user == 'all'){
		$log_user = '';
	}

	if($log_s_date == '' AND $log_e_date == ''){
		$sql = "SELECT * FROM oms_log WHERE who_name LIKE '%{$log_user}%' AND log_type LIKE '%{$log_type}%' ORDER BY id DESC LIMIT $log_page,100";
	}else if($log_s_date == '' or $log_e_date == ''){
		echo '';die;
	}else{
		$sql = "SELECT * FROM oms_log WHERE do_time >= '{$log_s_date}' AND do_time < '{$log_e_date}' AND who_name LIKE '%{$log_user}%' AND log_type LIKE '%{$log_type}%' ORDER BY id DESC LIMIT $log_page,100";
	}

	$res = $db->getAll($sql);
	echo json_encode($res);
}