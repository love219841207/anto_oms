<?php
// 日志
function oms_log($who_name,$who_do,$log_type,$station,$store){
	$db = new PdoMySQL();
	$sql="INSERT INTO oms_log (who_name,who_do,log_type,do_time,station,store)VALUES('{$who_name}','{$who_do}','{$log_type}',NULL,'{$station}','{$store}');";
	$res = $db->execute($sql);
}