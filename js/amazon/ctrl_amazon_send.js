var app=angular.module('myApp');
app.controller('amazon_send_Ctrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){
    $scope.format_order = function(){
    	$log.info($scope.now_store_bar)
    	// 格式化合单状态的订单
    	
    }
}])