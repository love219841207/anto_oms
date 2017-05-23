var app=angular.module('myApp');
app.controller('sendCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){

    $scope.send_table = function(type){

        $http.get('/fuck/common/make_send.php', {
            params:{
                send_table:'down',
                type:type,
                select_company:$scope.select_company,
                select_repo:$scope.select_repo,
                s_date:$scope.s_date,
                e_date:$scope.e_date
            }
        }).success(function(data) {
            $log.info(data);
            window.location="/down/"+data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载通信失败。");
        });
    }

    $scope.look_express = function(){
        $scope.shadow('open','ss_read','正在读取数据');
        $http.get('/fuck/common/import_express.php', {params:{look_express:'look'}
        }).success(function(data) {
            // console.log(data)
            $scope.express_response = data;
            $timeout(function(){$scope.shadow('close');},1000);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("快递单读取失败");
        });
    }


}])