var app=angular.module('myApp');
app.controller('repairCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){
    $scope.my_checked = [];
    $scope.search_field = '';
    $scope.s_date = '';
    $scope.e_date = '';

    // 移除shadow
    $scope.remove_shadow = function(){
        $timeout(function(){
            var dom = document.querySelector('.modal-backdrop');
            angular.element(dom).remove();
            // angular.element(dom).css({"z-index":'100'});
        },300);
    };

    //全选
    $scope.check_all_item = function(){
        angular.forEach($scope.repair_list, function(value, index){
            $scope.repair_list[index].is_click = true;
        });
        $scope.cc_all = false;
        $scope.check_items();
    };
    $scope.cc_all = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item = function(){
        angular.forEach($scope.repair_list, function(value, index){
            $scope.repair_list[index].is_click = false;
        });
        $scope.cc_all = true;
        $scope.check_items();
    };

    //反选
    $scope.check_back_item = function(){
        angular.forEach($scope.repair_list, function(value, index){
            if($scope.repair_list[index].is_click == true){
                $scope.repair_list[index].is_click = false;
            }else{
                $scope.repair_list[index].is_click = true;
            }
        });
        $scope.check_items();
    };

    //全选
    $scope.check_all_item2 = function(){
        angular.forEach($scope.repair_list2, function(value, index){
            $scope.repair_list2[index].is_click = true;
        });
        $scope.cc_all2 = false;
        $scope.check_items();
    };
    $scope.cc_all2 = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item2 = function(){
        angular.forEach($scope.repair_list2, function(value, index){
            $scope.repair_list2[index].is_click = false;
        });
        $scope.cc_all2 = true;
        $scope.check_items();
    };

    //反选
    $scope.check_back_item2 = function(){
        angular.forEach($scope.repair_list2, function(value, index){
            if($scope.repair_list2[index].is_click == true){
                $scope.repair_list2[index].is_click = false;
            }else{
                $scope.repair_list2[index].is_click = true;
            }
        });
        $scope.check_items();
    };

    //全选
    $scope.check_all_item3 = function(){
        angular.forEach($scope.repair_orders, function(value, index){
            $scope.repair_orders[index].is_click = true;
        });
        $scope.cc_all3 = false;
        $scope.check_items();
    };
    $scope.cc_all3 = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item3 = function(){
        angular.forEach($scope.repair_orders, function(value, index){
            $scope.repair_orders[index].is_click = false;
        });
        $scope.cc_all3 = true;
        $scope.check_items();
    };

    //反选
    $scope.check_back_item3 = function(){
        angular.forEach($scope.repair_orders, function(value, index){
            if($scope.repair_orders[index].is_click == true){
                $scope.repair_orders[index].is_click = false;
            }else{
                $scope.repair_orders[index].is_click = true;
            }
        });
        $scope.check_items();
    };

    //check_items 选择项
    $scope.check_items = function(){
        var my_checked = [];
        angular.forEach($scope.repair_list, function(value, index){
            if($scope.repair_list[index].is_click == true){
                my_checked.push("'"+$scope.repair_list[index].id+"'");
            }
        });
        angular.forEach($scope.repair_list2, function(value, index){
            if($scope.repair_list2[index].is_click == true){
                my_checked.push("'"+$scope.repair_list2[index].id+"'");
            }
        });
        angular.forEach($scope.repair_orders, function(value, index){
            if($scope.repair_orders[index].is_click == true){
                my_checked.push("'"+$scope.repair_orders[index].id+"'");
            }
        });
        $scope.check_all_num = my_checked.length;
        $scope.my_checked = my_checked;
        $scope.my_checked_items = my_checked.join(',');
        // $log.info($scope.my_checked);
    };

    // 售后拉取订单
    $scope.syn_repair_order = function(){
        $scope.shadow('open','ss_syn','正在拉取售后订单并验证自动格式化，请稍后...');
        var post_data = {
            syn_repair_order:'syn',
            s_date:$scope.s_date,
            e_date:$scope.e_date};
        $http.post('/fuck/repair/repair_import_order.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','拉取完成。','fa fa-smile-o');
                $scope.read_repair_order();
            }else{
                $log.info(data);
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:售后拉取订单。");
        });
    };

    // 删除订单
    $scope.del_repair_items = function(){
        $scope.shadow('open','ss_make','正在删除，请稍后。');

        var post_data = {del_repair_items:$scope.my_checked_items};

        $http.post('/fuck/repair/repair_import_order.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.read_repair_order();         
                $scope.plug_alert('success','删除完成。','fa fa-smile-o');
            }else{
                $log.info(data);
                $scope.plug_alert('danger','删除失败，请联系管理员。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除售后订单失败。");
        });
    };

    // 读取售后订单
    $scope.read_repair_order = function(){
        $http.get('/fuck/repair/repair_import_order.php', {
            params:{
                read_repair_order:'read'}
        }).success(function(data) {
            $scope.repair_list = data.res1;
            $scope.repair_list2 = data.res2;
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取售后订单失败。");
        });
    };

    // pass 到发货区
    $scope.pass_repair = function(){
        $http.get('/fuck/repair/repair_import_order.php', {
            params:{
                pass_repair:'pass'}
        }).success(function(data) {
            $scope.read_repair_order();         
            $scope.plug_alert('success','订单已到保修发货区。','fa fa-smile-o');
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:pass订单失败。");
        });
    };

    // 发货区读取数据
    $scope.read_pass_orders = function(){
        $http.get('/fuck/repair/repair_order.php', {
            params:{
                read_pass_orders:'read',
                s_date:$scope.s_date,
                e_date:$scope.e_date,
                search_field:$scope.search_field,
                search_key:$scope.search_key
            }
        }).success(function(data) {
            $scope.repair_orders = data;
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取售后订单失败。");
        });
    };

    // 删除订单
    $scope.del_repair_orders = function(){
        $scope.shadow('open','ss_make','正在删除，请稍后。');

        var post_data = {del_repair_orders:$scope.my_checked_items};

        $http.post('/fuck/repair/repair_order.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.read_pass_orders();         
                $scope.plug_alert('success','删除完成。','fa fa-smile-o');
            }else{
                $log.info(data);
                $scope.plug_alert('danger','删除失败，请联系管理员。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除售后订单失败。");
        });
    };

    // 售后发货
    $scope.sub_repair = function(){
        $scope.shadow('open','ss_write','正在扣库存，请稍后。');

        var post_data = {
            sub_repo:'repair',
            station:'repair',
            store:'repair'
        };
        $http.post('/fuck/common/list_order.php', post_data).success(function(data) {  
            if(data=='ok'){
                $scope.plug_alert('success','扣库完成。','fa fa-smile-o');
            }else{
                $log.info(data);
                $scope.plug_alert('danger','扣库失败。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:扣库存失败。");
        });
    };

}]);