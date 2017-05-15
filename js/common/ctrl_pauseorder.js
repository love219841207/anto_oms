var app=angular.module('myApp');
app.controller('pauseorderCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
     $scope.radioModel = 'Left';
	// 查询所有平台冻结订单info表
    $scope.show_pause_info = function(){
        $scope.all_pause_orders = '';
        $http.get('/fuck/common/pause_order.php', {
            params:{
                pause_order:'get'
            }
        }).success(function(data) {
            $scope.all_pause_orders = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:查询所有平台冻结订单info失败。");
        });
    }
    $scope.show_pause_info();

    // 查询冻结退押info表
    $scope.show_back_info = function(){
        $scope.all_pause_orders = '';
        $http.get('/fuck/common/pause_order.php', {
            params:{
                back_order:'get'
            }
        }).success(function(data) {
            $scope.all_pause_orders = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:查询所有平台退押订单info失败。");
        });
    }

    // 冻结订单表
    $scope.down_pauseorders = function(){
    	$scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/common/pause_order.php', {
            params:{
                down_pause_orders_table:'down'
            }
        }).success(function(data) {
        	// $log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_orders_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结订单表失败。");
        });
	}

    // 退押
    $scope.back_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                back_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_pause_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','退押失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:退押失败。");
        });
    }

    // 还原
    $scope.to_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                to_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_back_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','还原失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:退押失败。");
        });
    }

    // pause_modal
    $scope.pause_modal = function(id,store){
        // 查询单个详情
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                one_pause:id
            }
        }).success(function(data) {
            $scope.pause_modal_data = data;
            $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除失败。");
        });
    }

    // 删除
    $scope.del_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                del_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_back_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','删除失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除失败。");
        });
    }
}]);