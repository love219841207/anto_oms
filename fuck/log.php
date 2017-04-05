<?php
// 日志
function oms_log($who_name,$who_do,$log_type){
	$db = new PdoMySQL();
	$sql="INSERT INTO oms_log (who_name,who_do,log_type,do_time)VALUES('{$who_name}','{$who_do}','{$log_type}',NULL);";
	$res = $db->execute($sql);
}