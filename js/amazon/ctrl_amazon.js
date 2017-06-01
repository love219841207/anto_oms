app.controller('amazonCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log,){
	//	获取快递列表
	$scope.get_express_list = function(){
		alert(1)
		$scope.loading_shadow('open'); //打开loading
		$timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
	}

	// 上传快递单号
	$scope.send_express = function(){
		$scope.loading_shadow('open'); //打开loading
		$timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
		alert(2)
	}

	// 下载CSV
	$scope.down_express_csv = function(){
		$scope.loading_shadow('open'); //打开loading
		$timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
		alert(3)
	}
}]) 