app.controller('amazonCtrl', ['$scope','$state','$http','$log','$timeout', function($scope,$state,$http,$log,$timeout){
	//赋日期
    var myDate = new Date();
    var today = myDate.toLocaleDateString().replace("/","-");
    today = today.replace("/","-");
    $scope.s_date = today;
    $scope.e_date = today;
    $scope.init_express = function(){
        $scope.express_response = false;
    }

	//	获取快递列表
	$scope.get_express_list = function(){
		$scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/amazon/amazon_send_express.php', {
            params:{
            	get_express_list:'get',
                store:$scope.now_store_bar,
                s_date:$scope.s_date,
                e_date:$scope.e_date
            }
        }).success(function(data) {
            $scope.express_list = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取快递列表失败。");
        });
	}

	// 上传快递单号
	$scope.amz_send_express = function(){
		$scope.shadow('open','ss_syn','正在上传 '+$scope.now_store_bar+' 快递单');
		$http.get('/fuck/amazon/amazon_send_express.php', {
            params:{
                amz_send_express:$scope.now_store_bar
            }
        }).success(function(data) {
        	$log.info(data)
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载上传快递单失败。");
        });

	}

	// 下载xlsx
	$scope.down_express_xlsx = function(){
		$scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/amazon/amazon_send_express.php', {
            params:{
                down_express_xlsx:'down'
            }
        }).success(function(data) {
        	// $log.info(data)
            if(data == 'ok'){
            	window.location="/down/amz_uploads_express.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载上传快递单失败。");
        });
	}
}]) 