<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
// 展示订单列表参数
if(isset($_GET['list_order_count'])){
    $store = $_GET['list_order_count'];
    $station = strtolower($_GET['station']);
    $response_list = $station.'_response_list';
    // 查询标记数
    $sql = "SELECT count(1) as mark_count FROM $response_list WHERE store = '{$store}' AND is_mark = 1 AND order_line>0";
    $res = $db->getOne($sql);
    $mark_count = $res['mark_count'];

    // 查询无详单数
    $sql = "SELECT count(1) as no_info_count FROM $response_list WHERE store = '{$store}' AND order_line = 0";
    $res = $db->getOne($sql);
    $no_info_count = $res['no_info_count'];

    // 查询卡邮编数
    $sql = "SELECT count(1) as no_post_count FROM $response_list WHERE store = '{$store}' AND post_ok = 2 AND order_line>0";
    $res = $db->getOne($sql);
    $no_post_count = $res['no_post_count'];

    // 查询卡电话数
    $sql = "SELECT count(1) as no_tel_count FROM $response_list WHERE store = '{$store}' AND tel_ok = 2 AND order_line>0";
    $res = $db->getOne($sql);
    $no_tel_count = $res['no_tel_count'];

    // 查询卡sku数
    $sql = "SELECT count(1) as no_sku_count FROM $response_list WHERE store = '{$store}' AND sku_ok = 2 AND order_line>0";
    $res = $db->getOne($sql);
    $no_sku_count = $res['no_sku_count'];

    // 查询卡运费代码数
    $sql = "SELECT count(1) as no_yfcode_count FROM $response_list WHERE store = '{$store}' AND yfcode_ok = 2 AND order_line>0";
    $res = $db->getOne($sql);
    $no_yfcode_count = $res['no_yfcode_count'];

    //final_res
    $final_res['mark_count'] = $mark_count;
    $final_res['no_info_count'] = $no_info_count;
    $final_res['no_post_count'] = $no_post_count;
    $final_res['no_tel_count'] = $no_tel_count;
    $final_res['no_sku_count'] = $no_sku_count;
    $final_res['no_yfcode_count'] = $no_yfcode_count;
    echo json_encode($final_res);
}

//查询订单总数
if(isset($_POST['items_count'])){
    $store = $_POST['items_count'];
    $station = strtolower($_POST['station']);
    $search_date = $_POST['search_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $search_order_line = $_POST['search_order_line'];
    $search_field = $_POST['search_field'];
    $search_key = addslashes($_POST['search_key']);

    $response_list = $station.'_response_list';

    // 标记订单查询
    if($search_order_line == 'mark' or $search_order_line == 'post_ok' or $search_order_line == 'tel_ok' or $search_order_line == 'sku_ok' or $search_order_line == 'yfcode_ok'){
    	if($search_order_line == 'mark'){
    		$sql = "SELECT count(1) as cc FROM $response_list WHERE is_mark = '1' AND store = '{$store}' AND order_line>0";
    	}
    	if($search_order_line == 'post_ok'){
    		$sql = "SELECT count(1) as cc FROM $response_list WHERE post_ok = 2 AND store = '{$store}' AND order_line>0";
    	}
    	if($search_order_line == 'tel_ok'){
    		$sql = "SELECT count(1) as cc FROM $response_list WHERE tel_ok = 2 AND store = '{$store}' AND order_line>0";
    	}
    	if($search_order_line == 'sku_ok'){
    		$sql = "SELECT count(1) as cc FROM $response_list WHERE sku_ok = 2 AND store = '{$store}' AND order_line>0";
    	}
    	if($search_order_line == 'yfcode_ok'){
    		$sql = "SELECT count(1) as cc FROM $response_list WHERE yfcode_ok = 2 AND store = '{$store}' AND order_line>0";
    	}
    }else{
    	if($search_field == ''){   //0没有筛选条件
    		//是否是全部订单
    		if($search_order_line == 'all'){  //所有单不包括回收站的
    			if($start_date =='' or $end_date ==''){  
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE store = '{$store}' AND order_line>0";
		        }else{
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE store = '{$store}' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}' AND order_line>0";
		        }
    		}else{
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}'";
		        }else{
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}'";
		        }
    		}
	    }else{
	    	//是否是全部订单
    		if($search_order_line == 'all'){  //所有单不包括回收站的
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' AND order_line>0";
		        }else{
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}' AND order_line>0";
		        }
    		}else{
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND {$search_field} LIKE '%{$search_key}%'";
		        }else{
		            $sql = "SELECT count(1) as cc FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}'";
		        }
    		}
	    }
    }

    $res = $db->getOne($sql);
    echo $res['cc'];
    
}

//查询订单列表数据
if(isset($_POST['get_order_list'])){
    $store = $_POST['get_order_list'];
    $station = strtolower($_POST['station']);
    $page_size = $_POST['page_size'];
    $start = $_POST['start'];
    $search_order_line = $_POST['search_order_line'];
    $search_date = $_POST['search_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $search_field = $_POST['search_field'];
    $search_key = addslashes($_POST['search_key']);

    $response_list = $station.'_response_list';

    // 标记订单查询
    if($search_order_line == 'mark' or $search_order_line == 'post_ok' or $search_order_line == 'tel_ok' or $search_order_line == 'sku_ok' or $search_order_line == 'yfcode_ok'){
    	if($search_order_line == 'mark'){
    		$sql = "SELECT * FROM $response_list WHERE is_mark = '1' AND store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
    	}
    	if($search_order_line == 'post_ok'){
    		$sql = "SELECT * FROM $response_list WHERE post_ok = 2 AND store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
    	}
    	if($search_order_line == 'tel_ok'){
    		$sql = "SELECT * FROM $response_list WHERE tel_ok = 2 AND store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
    	}
    	if($search_order_line == 'sku_ok'){
    		$sql = "SELECT * FROM $response_list WHERE sku_ok = 2 AND store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
    	}
    	if($search_order_line == 'yfcode_ok'){
    		$sql = "SELECT * FROM $response_list WHERE yfcode_ok = 2 AND store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
    	}
    }else{
    	if($search_field == ''){   //0没有筛选条件
    		//是否是全部订单
    		if($search_order_line == 'all'){
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT * FROM $response_list WHERE store = '{$store}' AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
		        }else{
		            $sql = "SELECT * FROM $response_list WHERE store = '{$store}' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}'  AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
		        }
    		}else{
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT * FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' ORDER BY id DESC limit {$start},{$page_size}";
		        }else{
		            $sql = "SELECT * FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}' ORDER BY id DESC limit {$start},{$page_size}";
		        }
    		}
	    }else{
	    	//是否是全部订单
    		if($search_order_line == 'all'){
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT * FROM $response_list WHERE store = '{$store}' AND {$search_field} LIKE '%{$search_key}%'  AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
		        }else{
		            $sql = "SELECT * FROM $response_list WHERE store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}'  AND order_line>0 ORDER BY id DESC limit {$start},{$page_size}";
		        }
    		}else{
    			if($start_date =='' or $end_date ==''){
		            $sql = "SELECT * FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' ORDER BY id DESC limit {$start},{$page_size}";
		        }else{
		            $sql = "SELECT * FROM $response_list WHERE order_line = '{$search_order_line}' AND store = '{$store}' AND {$search_field} LIKE '%{$search_key}%' AND $search_date >= '{$start_date}' AND $search_date <'{$end_date}' ORDER BY id DESC limit {$start},{$page_size}";
		        }
    		}
	        
	    }
    }
    $res = $db->getAll($sql);
    foreach ($res as $key => $value) {
    	$res[$key]['is_click'] = false;
    }

	echo json_encode($res);
}

// ------------------------- 查询ready_send数据 -------------------------

// 查询ready_send数目
if(isset($_POST['ready_send_count'])){
    $search_field = $_POST['search_field'];
    $search_key = addslashes($_POST['search_key']);

    if($search_field == ''){   //0没有筛选条件  
        $sql = "SELECT count(1) as cc FROM send_table WHERE item_line = 0";
    }else{
        $sql = "SELECT count(1) as cc FROM send_table WHERE item_line = 0 AND {$search_field} LIKE '%{$search_key}%'";
    }
    
    $res = $db->getOne($sql);
    echo $res['cc'];
}

//查询订单列表数据
if(isset($_POST['ready_send_data'])){
    $page_size = $_POST['page_size'];
    $start = $_POST['start'];
    $search_field = $_POST['search_field'];
    $search_key = addslashes($_POST['search_key']);

    if($search_field == ''){   //0没有筛选条件
        $sql = "SELECT * FROM send_table WHERE item_line = 0 ORDER BY send_id DESC LIMIT {$start},{$page_size}";
    }else{
        $sql = "SELECT * FROM send_table WHERE item_line = 0 AND {$search_field} LIKE '%{$search_key}%' ORDER BY send_id DESC LIMIT {$start},{$page_size}";
    }
    $res = $db->getAll($sql);
    echo json_encode($res);
}