var app=angular.module('myApp');
//登陆控制器
app.controller('loginCtrl', ['$rootScope','$scope','$state','$stateParams','$http','$log','$timeout',function ($rootScope,$scope,$state,$stateParams,$http,$log,$timeout) {
    $rootScope.bg = true;   //设置背景
    //欢迎词
    if($stateParams.respond == 'timeout'){
        $timeout(function(){
            $scope.plug_alert('danger','您已超时，请重新登录。','fa fa-clock-o');
        },1000);
    }else if($stateParams.respond == 'first'){
        $timeout(function(){
            $scope.plug_alert('info','你好，请登录。','fa fa-smile-o');
        },1000);
    }else if($stateParams.respond == 'logout'){
        $timeout(function(){
            $scope.plug_alert('info','您已退出，请重新登录。','fa fa-smile-o');
        },1000);
    }

    $scope.save = function(){
        var post_data = {u_num:$scope.u_num,u_pwd:$scope.u_pwd};
        if($scope.loginForm.$valid){
            $http.post('/fuck/login.php', post_data).success(function(data) {  
                console.log(data)
                if(data=='go'){
                    $state.go('site',{respond:'hello'});  //跳转到site
                }else{
                    $scope.plug_alert('danger','账号或密码验证失败，请重新登录。','fa fa-ban');
                    $log.info('账号或密码验证失败，请重新登录。');
                }
            }).error(function(data) {  
                $scope.plug_alert('danger','登陆失败，请联系管理员。','fa fa-bug');
                $log.info('登陆失败，请联系管理员。');
            });  
        }else{
            $scope.plug_alert('danger','格式验证失败。','fa fa-bell');
            $log.info('格式验证失败');
        }
    }
}])