var app=angular.module('myApp');
app.controller('amazon_send_Ctrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){

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
    	$scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/amazon/amazon_make_orders.php', {
            params:{
                format_order:$scope.now_store_bar
            }
        }).success(function(data) {
            if(data == 'ok'){
            	$scope.fu_bag();
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
    	if($scope.repo_status == '1'){
	        $http.get('/fuck/amazon/amazon_make_orders.php', {
	            params:{
	                fu_bag:$scope.now_store_bar
	            }
	        }).success(function(data) {
	            if(data == 'ok'){
	            	$scope.check_sku();
	            }else{
	            	$scope.plug_alert('danger','拆福袋/别名失败。','fa fa-ban');
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
    	if($scope.repo_status == '1'){
	        $http.get('/fuck/amazon/amazon_make_orders.php', {
	            params:{
	                check_sku:$scope.now_store_bar
	            }
	        }).success(function(data) {
	            if(data == 'ok'){
	            	$scope.read_format_table();
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
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取格式化订单失败。");
        });
    }

}])