var app=angular.module('myApp');
app.controller('yfcodeCtrl', ['$scope','$state','$http','$log','$timeout', function($scope,$state,$http,$log,$timeout){
	// 检测正数
    $scope.check_int = function(e){
        var num = $scope[e];
        if(num < 0 || num == ''){
            $scope[e] = '';
            $scope.plug_alert('danger','请输入大于 0 的数。','fa fa-ban');
        }else{
        }
    }

    // 读取运费代码表
    $scope.get_table = function(){
    	$scope.loading_shadow('open'); //打开loading
    	$http.get('/fuck/systems/yf_code.php', {
        	params:{
        		get_table:'get'
        	}
        }).success(function(data) {
            $scope.yfcode_table = data;
 			$timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }
    $scope.get_table();


    // 添加运费代码
    $scope.add_yfcode = function(){
    	if($scope.yf_code == undefined || $scope.yf_code == '')return false;
    	if($scope.level == undefined || $scope.level == '')return false;
    	if($scope.send_method == undefined || $scope.send_method == '')return false;
    	if($scope.need_cod == undefined || $scope.need_cod == '')return false;
    	if($scope.default_yf == undefined || $scope.default_yf == '')return false;
    	if($scope.default_one_yf == undefined || $scope.default_one_yf == '')return false;

    	$scope.loading_shadow('open'); //打开loading

        $http.get('/fuck/systems/yf_code.php', {
        	params:{
        		add_yfcode:$scope.yf_code,
	            level:$scope.level,
	            send_method:$scope.send_method,
	            need_cod:$scope.need_cod,
	            default_yf:$scope.default_yf,
	            default_one_yf:$scope.default_one_yf
        	}
        }).success(function(data) {
            if(data == 'ok'){
            	$scope.get_table();
            }else{
            	$scope.plug_alert('danger',data,'fa fa-ban');
            }
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }

    // 开启关闭状态及COD
    $scope.change_status = function(id,field){
    	$scope.loading_shadow('open'); //打开loading
    	$http.get('/fuck/systems/yf_code.php', {
        	params:{
        		change_status:id,
        		field:field
        	}
        }).success(function(data) {
        	if(data == 'ok'){
        		$scope.get_table();
        	}else{
        		$scope.plug_alert('danger',data,'fa fa-ban');
        	}
 			$timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }
}])