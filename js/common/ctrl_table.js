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
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取冻结表失败。");
        });
	};

	// 下载冻结表
	$scope.down_pause = function(){
		$http.get('/fuck/table/pause_table.php', {
            params:{
                down_pause:'down'
            }
        }).success(function(data) {
        	$log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_table.xlsx";
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结表失败。");
        });
	}
}])