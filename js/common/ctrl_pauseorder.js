var app=angular.module('myApp');
app.controller('pauseorderCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
	//查询所有平台冻结订单info表
    $scope.show_send_info = function(){
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
    $scope.show_send_info();

    // 冻结订单表
    $scope.down_pauseorders = function(){
    	$scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/common/pause_order.php', {
            params:{
                down_pause_orders_table:'down'
            }
        }).success(function(data) {
        	$log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_orders_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结订单表失败。");
        });
	}
}]);