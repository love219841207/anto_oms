var app=angular.module('myApp');
app.controller('yfcodeCtrl', ['$scope','$state','$http','$log','$timeout', function($scope,$state,$http,$log,$timeout){
	// 检测正数
    $scope.check_int = function(e){
        var num = $scope[e];
        if(num == 0){

        }else{
            if(num < 0 || num == ''){
            $scope[e] = '';
                $scope.plug_alert('danger','输入有误。','fa fa-ban');
            }else{
            }
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
    	// if($scope.default_yf == undefined || $scope.default_yf == '')return false;
    	// if($scope.default_one_yf == undefined || $scope.default_one_yf == '')return false;

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

    // 删除运费代码
    $scope.del_yfcode = function(id){
    	$scope.loading_shadow('open'); //打开loading
    	$http.get('/fuck/systems/yf_code.php', {
        	params:{
        		del_yfcode:id
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

    // 读取地区
    $scope.get_area = function(){
        $http.get('/fuck/systems/yf_code.php', {
            params:{
                get_area:$scope.change_yfcode
            }
        }).success(function(data) {
            $scope.jp_area = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }
    $scope.get_area();

    // 特殊运费代码 - - - - - - - - - - - - - - - - - - -

    // 获取
    $scope.get_spe_list = function(yfcode){
        $http.get('/fuck/systems/yf_code.php', {
            params:{
                get_spe_list:yfcode
            }
        }).success(function(data) {
            $scope.spe_list_data = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }

    // 新增
    $scope.add_spe_yfcode = function(){
        $http.get('/fuck/systems/yf_code.php', {
            params:{
                add_spe_yfcode:$scope.change_yfcode,
                add_area:$scope.add_area,
                spe_yf:$scope.spe_yf,
                spe_one_yf:$scope.spe_one_yf
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.get_area();
                $scope.add_area = '';
                $scope.get_spe_list($scope.change_yfcode);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }

    // 删除
    $scope.del_spe = function(id){
        $http.get('/fuck/systems/yf_code.php', {
            params:{
                del_spe:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.get_spe_list($scope.change_yfcode);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
        });
    }


}])