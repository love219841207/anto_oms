var app=angular.module('myApp');
app.controller('versionCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){
	//更新版本号
    $scope.updte_version = function(){
    	var time=new Date().getTime()
        $http.get('/fuck/topbar.php', {params:{updte_version:$scope.new_version}
        }).success(function(data) {
            if (data == 'ok') {
            	$state.go('site.sys_version','',{reload:true});           	
            }else{
            	$scope.plug_alert('danger','版本更新失败。');
            	$log.info("版本更新失败。");
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:更新版本号。");
        });
    }
}])