<?php
// 日志
function oms_log($who_name,$who_do,$log_type,$station,$store,$oms_id){
	if($oms_id == '-'){
		$oms_id = 0;
	}
	$db = new PdoMySQL();
	$sql="INSERT INTO oms_log (who_name,who_do,log_type,do_time,station,store,oms_id)VALUES('{$who_name}','{$who_do}','{$log_type}',NULL,'{$station}','{$store}','{$oms_id}');";
	$res = $db->execute($sql);
}