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


    // 读取格式化订单表
    $scope.read_format_table = function(){
        $http.get('/fuck/amazon/amazon_make_orders.php', {
            params:{
                read_format_table:$scope.now_store_bar
            }
        }).success(function(data) {
        	$scope.check_format_ok();	//验证格式化表error
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
                $scope.check_format_ok();
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

    // 查看格式化表是否通过error
    $scope.check_format_ok =  function(){
    	$http.get('/fuck/amazon/amazon_make_orders.php', {params:{check_format_ok:$scope.now_store_bar}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.format_ok = true;
            }else if(data == 'no'){
            	$scope.format_ok = false;
            }else{
                $scope.plug_alert('danger','格式化表通过验证失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:格式化表通过验证失败。");
        });
    }

}])