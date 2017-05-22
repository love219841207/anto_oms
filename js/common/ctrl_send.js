var app=angular.module('myApp');
app.controller('sendCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){

    $scope.send_table = function(type){

        if($scope.s_date == $scope.e_date){
            $scope.plug_alert('danger','警告，日期区间不能相同。','fa fa-exclamation-triangle');
            return false;
        }

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
            
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载通信失败。");
        });
    }

}])