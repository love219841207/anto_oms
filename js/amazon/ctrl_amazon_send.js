var app=angular.module('myApp');
app.controller('amazon_send_Ctrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){
	$scope.init_list = function(){
		$scope.format_table = '';
	}

	// 连接库存系统
    $scope.ping_repo = function(){
        $http.get('/fuck/ping_repo.php', {params:{ping_repo:"ping"}
        }).success(function(data) {
            if(data.repo_status == 1){
                $scope.plug_alert('success','已连接库存系统。','fa fa-link');
                $scope.repo_status = '1';
            }else{
            	$scope.plug_alert('warning','库存系统连接失败。','fa fa-unlink');
                $scope.repo_status = '0';
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("格式化连接repo失败。");
        });
    }
    $scope.ping_repo();

	// 格式化合单状态的订单
    $scope.format_order = function(){
    	$scope.init_list();
    	$scope.shadow('open','ss_syn','正在导入合单列表');
        $http.get('/fuck/amazon/amazon_make_orders.php', {
            params:{
                format_order:$scope.now_store_bar
            }
        }).success(function(data) {
            if(data == 'ok'){
                $timeout(function(){$scope.fu_bag();},2000);
            }else{
            	$scope.plug_alert('danger','格式化失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:格式化失败。");
        });
    }

    // 拆福袋和别名-连接Repo
    $scope.fu_bag = function(){
    	$scope.read_format_table();
    	$scope.shadow('open','ss_write','正在拆福袋 / 别名 SKU');
    	if($scope.repo_status == '1'){
	        $http.get('/fuck/amazon/amazon_make_orders.php', {
	            params:{
	                fu_bag:$scope.now_store_bar
	            }
	        }).success(function(data) {
	            if(data == 'ok'){
	            	$timeout(function(){$scope.check_sku();},2000);
	            }else{
	            	$scope.plug_alert('danger','拆福袋 / 别名失败。','fa fa-ban');
	            }
	        }).error(function(data) {
	            alert("系统错误，请联系管理员。");
	            $log.info("error:拆福袋失败。");
	        });
    	}else{
    		$scope.plug_alert('warning','库存系统连接失败。','fa fa-unlink');
    	}
    	
    }

    // 验证sku-连接Repo
    $scope.check_sku = function(){
    	$scope.read_format_table();
    	$scope.shadow('open','ss_write','正在检测 SKU');
    	if($scope.repo_status == '1'){
	        $http.get('/fuck/amazon/amazon_make_orders.php', {
	            params:{
	                check_sku:$scope.now_store_bar
	            }
	        }).success(function(data) {
	            if(data == 'ok'){
	            	$timeout(function(){$scope.shadow('open','ss_read','即将完成');},2000);
        			$timeout(function(){$scope.read_format_table();$scope.shadow('close');},4000); //关闭shadow
	            }else{
	            	$scope.plug_alert('danger','sku验证失败。','fa fa-ban');
	            }
	        }).error(function(data) {
	            alert("系统错误，请联系管理员。");
	            $log.info("error:sku验证失败。");
	        });
    	}else{
    		$scope.plug_alert('warning','库存系统连接失败。','fa fa-unlink');
    	}
    	
    }

    // 读取格式化订单表
    $scope.read_format_table = function(){
        $http.get('/fuck/amazon/amazon_make_orders.php', {
            params:{
                read_format_table:$scope.now_store_bar
            }
        }).success(function(data) {
        	
            $scope.format_table = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取格式化订单失败。");
        });
    }

    //修改格式化List单个字段
    $scope.change_format_field = function(field_name,id,old_key,oms_id){
        var dom = document.querySelector('#'+field_name+id);
        var new_key = angular.element(dom).val();
        // $log.info('#'+field_name+id);
        // $log.info(new_key);
        $http.get('/fuck/amazon/amazon_make_orders.php', {params:{change_format_field:id,field_name:field_name,new_key:new_key,old_key:old_key,oms_id}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.read_format_table();
            }else if(data == 'no_has'){
            	$scope.plug_alert('danger','无此商品代码','fa fa-ban');
            }else{
                $scope.plug_alert('danger','格式化字段修改失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:格式化字段修改失败。");
        });
    }

}])