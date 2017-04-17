<?php
require_once("../header.php");

//查询店铺
if(isset($_GET['get_store'])){
	$sql = "SELECT * FROM oms_store ORDER BY station";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

//添加店铺
if(isset($_GET['new_store'])){
	$new_store = addslashes($_GET['new_store']);
	$station = addslashes($_GET['station']);
	$sql = "SELECT * FROM oms_store WHERE store_name = '{$new_store}'";
    $res = $db->getOne($sql);
	if(empty($res)){
		//新建店铺
		$sql = "INSERT INTO oms_store (station,store_name) VALUES ('{$station}','{$new_store}')";
		$res = $db->execute($sql);
		//新建店铺配置
		if($station == 'Amazon'){
			$sql = "INSERT INTO conf_Amazon (store_name) VALUES ('{$new_store}')";
			$res = $db->execute($sql);
		}
		//其他平台！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
		echo 'ok';
	}else{
		echo 'has';
	}
}

//查询店铺配置
if(isset($_GET['get_conf'])){
	$station = $_GET['get_conf'];
	$station = 'conf_'.$station;
	$store_name = $_GET['store_name'];
	$sql = "SELECT * FROM {$station} WHERE store_name = '{$store_name}'";
    $res = $db->getOne($sql);
    echo json_encode($res);  
}

//更新店铺配置
if(isset($_GET['update_conf'])){
	$station = $_GET['update_conf'];
	$store_name = $_GET['store_name'];
	$awsaccesskeyid = $_GET['awsaccesskeyid'];
	$sellerid = $_GET['sellerid'];
	$signatureversion = $_GET['signatureversion'];
	$secret = $_GET['secret'];
	$marketplaceid_id_1 = $_GET['marketplaceid_id_1'];
	$mail_name = $_GET['mail_name'];
	$mail_id = $_GET['mail_id'];
	$mail_pwd = $_GET['mail_pwd'];
	$mail_smtp = $_GET['mail_smtp'];
	$mail_port = $_GET['mail_port'];
	$mail_answer_addr = $_GET['mail_answer_addr'];
	$mail_over_send = $_GET['mail_over_send'];
	//判断店铺
	if($station == 'Amazon'){
		$sql = "UPDATE conf_Amazon SET awsaccesskeyid = '{$awsaccesskeyid}',sellerid = '{$sellerid}',signatureversion = '{$signatureversion}',secret = '{$secret}',marketplaceid_id_1 = '{$marketplaceid_id_1}',mail_name = '{$mail_name}',mail_id = '{$mail_id}',mail_pwd = '{$mail_pwd}',mail_smtp = '{$mail_smtp}',mail_port = '{$mail_port}',mail_answer_addr = '{$mail_answer_addr}',mail_over_send = '{$mail_over_send}' WHERE store_name = '{$store_name}'";
	}
	//其他平台！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
    $res = $db->execute($sql);
    echo 'ok';
}

//删除店铺
if(isset($_GET['del_store'])){
	$del_store = $_GET['del_store'];
	$sql = "DELETE FROM oms_store WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//删除配置
	$sql = "DELETE FROM conf_Amazon WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//删除邮件模板
	$sql = "DELETE FROM mail_tpl WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//这里以后要删除其他平台的配置。！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
	echo 'ok';
}

//保存发货通知信模板
if(isset($_POST['save_express_mail'])){
	$store = addslashes($_POST['save_express_mail']);
	$mail_topic = addslashes($_POST['mail_topic']);
	$express_mail_html = addslashes($_POST['express_mail_html']);
	$express_mail_txt = addslashes($_POST['express_mail_txt']);
	//查询是否有此模板
	$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
	$res = $db->getOne($sql);
	if(empty($res)){
		$sql = "INSERT INTO mail_tpl (store_name,model_name,mail_topic,mail_html,mail_txt) VALUES ('{$store}','send_express','{$mail_topic}','{$express_mail_html}','{$express_mail_txt}')";
		$res = $db->execute($sql);
	}else{
		$sql = "UPDATE mail_tpl set mail_topic = '{$mail_topic}',mail_html = '{$express_mail_html}',mail_txt = '{$express_mail_txt}' WHERE store_name = '{$store}' AND model_name = 'send_express'";
		$res = $db->execute($sql);
	}
	echo 'ok';
}

//读取发货通知信模板
if(isset($_GET['get_express_mail'])){
	$store = $_GET['get_express_mail'];
	$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
	$res = $db->getOne($sql);
	echo json_encode($res);
}

//新增店铺邮件模板
if(isset($_GET['add_mail_tpl'])){
	$new_tpl = addslashes($_GET['add_mail_tpl']);
	$store = $_GET['mail_tpl_store'];
	$sql = "INSERT INTO mail_tpl (store_name,model_name,mail_topic,mail_html,mail_txt) VALUES ('{$store}','{$new_tpl}','','','')";
	$res = $db->execute($sql);
	echo 'ok';
}

//重命名店铺邮件模板
if(isset($_GET['rename_mail_tpl'])){
	$new_name = addslashes($_GET['new_name']);
	$id = $_GET['rename_mail_tpl'];
	$sql = "UPDATE mail_tpl SET model_name = '{$new_name}' WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//删除邮件模板
if(isset($_GET['del_mail_tpl'])){
	$id = $_GET['del_mail_tpl'];
	$sql = "DELETE FROM mail_tpl WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//读取、编辑邮件模板
if(isset($_GET['edit_mail_tpl'])){
	$id = $_GET['edit_mail_tpl'];
	$sql = "SELECT * FROM mail_tpl WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	echo json_encode($res);
}

//保存邮件模板
if(isset($_POST['save_mail_tpl'])){
	$id = addslashes($_POST['save_mail_tpl']);
	$mail_topic = addslashes($_POST['mail_topic']);
	$mail_html = addslashes($_POST['mail_html']);
	$mail_txt = addslashes($_POST['mail_txt']);

	$sql = "UPDATE mail_tpl set mail_topic = '{$mail_topic}',mail_html = '{$mail_html}',mail_txt = '{$mail_txt}' WHERE id = '{$id}'";
	$res = $db->execute($sql);

	echo 'ok';
}

//读取店铺邮件模板
if(isset($_GET['read_mail_tpl'])){
	$store = $_GET['read_mail_tpl'];
	$sql = "SELECT id,model_name FROM mail_tpl WHERE store_name = '{$store}' AND model_name <> 'send_express'";	#排除 express_mail
	$res = $db->getAll($sql);
	echo json_encode($res);
}