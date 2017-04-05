var app=angular.module('myApp');
app.controller('changepwdCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){
	//密码修改
    $scope.change_pwd = function(){
        $log.info($scope.old_pwd+$scope.new_pwd+$scope.re_new_pwd)
        $http.get('/fuck/user_conf/change_pwd.php', {params:{change_pwd:$scope.old_pwd,new_pwd:$scope.new_pwd,re_new_pwd:$scope.re_new_pwd}
        }).success(function(data) {
            if(data == 'ok') {
                $scope.plug_alert('success','密码修改完成，请重新登录。','fa fa-smile-o');
                window.location.href='/fuck/login.php?logout';
            }else if(data == 'error_re'){
                $scope.plug_alert('danger','新密码两次输入不一致。','fa fa-ban');
            }else if(data == 'error_old'){
            	$scope.plug_alert('danger','现有密码验证失败。','fa fa-ban');
            }else if(data == 'no_change'){
                $scope.plug_alert('danger','新旧密码不能一样。','fa fa-ban');
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:密码修改失败。");
        });
    }
}])