var app=angular.module('myApp');
app.controller('yfcodeCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){
	// 检测正数
    $scope.check_int = function(e){
        var num = $scope[e];
        if(num < 0 || num == ''){
            $scope[e] = '';
            $scope.plug_alert('danger','请输入大于 0 的数。','fa fa-ban');
        }else{
        }
    }

    // 添加运费代码
    $scope.add_yfcode = function(){
    	var post_data = {
            yf_code:$scope.yf_code,
            level:$scope.level,
            send_method:$scope.send_method,
            need_cod:$scope.need_cod,
            default_yf:$scope.default_yf,
            default_one_yf:$scope.default_one_yf,
        };
        $log.info(post_data)
    }
}])