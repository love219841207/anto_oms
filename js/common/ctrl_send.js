var app=angular.module('myApp');
app.controller('sendCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){
    //赋日期
    var myDate = new Date();
    var today = myDate.toLocaleDateString().replace("/","-");
    today = today.replace("/","-");
    $scope.s_date = today;
    $scope.e_date = today;
    $scope.init_express = function(){
        $scope.express_response = false;
    };

    // 下载发货单
    $scope.send_table = function(type,company){
        $scope.shadow('open','ss_make','正在生成发货单');

        var post_data = {
            send_table:'down',
            type:type,
            select_company:company,
            select_repo:$scope.select_repo,
            s_date:$scope.s_date,
            e_date:$scope.e_date
        };

        $http.post('/fuck/common/make_send.php', post_data).success(function(data) {
            // $log.info(data);
            window.location="/down/"+data;
            $timeout(function(){$scope.shadow('close');},1000);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载通信失败。");
        });
    };

    // 读取快递单
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
    };

    // 清空快递单
    $scope.truncate_yes = function(){
        $scope.shadow('open','ss_make','..正在清空');
        $http.get('/fuck/common/import_express.php', {params:{truncate_yes:'truncate'}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.look_express();
                $scope.truncate_btn = false;
                $timeout(function(){$scope.shadow('close');},1000);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("快递单清空失败");
        });
    };

    // 更新快递单
    $scope.up_express_order = function(){
        $scope.shadow('open','ss_make','..正在更新');
        $http.get('/fuck/common/import_express.php', {params:{up_express_order:'update'}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.look_express();
            }else{
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("更新OMS快递单失败");
        });
    };

    // 更新售后快递单
    $scope.up_repair_express = function(){
        $scope.shadow('open','ss_make','..正在删除 OMS 快递单');
        $timeout(function(){$scope.shadow('open','ss_syn','..正在更新快递单到【售后系统】');},1000);
        $http.get('/fuck/common/import_express.php', {params:{up_repair_express:'update'}
        }).success(function(data) {
            if(data == 'ok'){
                $timeout(function(){$scope.shadow('open','ss_write','更新完成');},3000);
                $timeout(function(){$scope.look_express();},6000);
            }else{
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("更新售后快递单失败");
        });
    };

}]);