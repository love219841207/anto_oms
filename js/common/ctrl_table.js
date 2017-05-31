var app=angular.module('myApp');
app.controller('tableCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){

	// 查看冻结表
	$scope.look_pause = function(){
		$scope.pause_table = false;
		$http.get('/fuck/table/pause_table.php', {
            params:{
                look_pause:'get'
            }
        }).success(function(data) {
            $scope.pause_table = data;
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取冻结表失败。");
        });
	};

	// 下载冻结表
	$scope.down_pause = function(){
        $scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/table/pause_table.php', {
            params:{
                down_pause:'down'
            }
        }).success(function(data) {
        	// $log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结表失败。");
        });
	}

    // 单品统计
    $scope.oue_table = function(){
        $scope.table_type;
        $scope.s_date;
        $scope.e_date;
        $scope.goods_code;
    }

    // 商品代码检测
    $scope.tip_goods_code = function(){
        
        $http.get('/fuck/common/check_order.php', {
            params:{
                tip_goods_code:$scope.goods_code
            }
        }).success(function(data) {
            $scope.goods_codes = data;
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:商品代码检测失败。");
        });
    }

    // 选中商品代码
    $scope.click_code = function(e){
        $scope.goods_code = e;
        $scope.goods_codes = false;
    }

}])