var app=angular.module('myApp');
app.controller('pauseorderCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
     $scope.radioModel = 'Left';

	// 查询所有平台冻结订单info表
    $scope.show_pause_info = function(){
        // $scope.all_pause_orders = '';
        $http.get('/fuck/common/pause_order.php', {
            params:{
                pause_order:'get'
            }
        }).success(function(data) {
            $scope.all_pause_orders = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:查询所有平台冻结订单info失败。");
        });
    }
    $scope.show_pause_info();

    // 查询冻结退押info表
    $scope.show_back_info = function(){
        // $scope.all_pause_orders = '';
        $http.get('/fuck/common/pause_order.php', {
            params:{
                back_order:'get'
            }
        }).success(function(data) {
            $scope.all_pause_orders = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:查询所有平台退押订单info失败。");
        });
    }

    // 冻结订单表
    $scope.down_pauseorders = function(){
    	$scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/common/pause_order.php', {
            params:{
                down_pause_orders_table:'down'
            }
        }).success(function(data) {
        	// $log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_orders_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结订单表失败。");
        });
	}

    // 退押
    $scope.back_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                back_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_pause_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','退押失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:退押失败。");
        });
    }

    // 还原
    $scope.to_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                to_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_back_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','还原失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:退押失败。");
        });
    }

    // pause_modal
    $scope.pause_modal = function(id,store){
        $scope.repo_num = '';
        // 查询单个详情
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                one_pause:id
            }
        }).success(function(data) {
            $scope.pause_modal_data = data;
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除失败。");
        });
    }

    // 删除
    $scope.del_pause = function(id,store){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                del_pause:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_back_info();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','删除失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除失败。");
        });
    }


    // 检测库存
    $scope.check_repo = function(goods_code){
        $http.get('/fuck/common/ready_send.php', {
            params:{
                check_repo:goods_code
            }
        }).success(function(data) {
            $scope.repo_num = data.repo;
            $scope.a_repo_num = data.a_repo;
            $scope.b_repo_num = data.b_repo;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:库存检测失败。");
        });
    }

    // 检测正数
    $scope.check_int = function(e){
        var dom = document.querySelector('#'+e);
        var num = angular.element(dom).val();
        if(num < 0 || num == ''){
            angular.element(dom).val('');
            $scope.plug_alert('danger','请输入大于 0 的数。','fa fa-ban');
        }
    }

    // 查询店铺对应平台
    $scope.get_station = function(store){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                get_station:store
            }
        }).success(function(data) {
            $scope.this_station = data;

        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:平台获取失败。");
        });

    }

    // 价格计算
    $scope.play_price = function(order_id){
        $http.get('/fuck/common/change_order.php', {
            params:{
                play_price:'play',
                station:$scope.this_station,
                order_id:order_id
            }
        }).success(function(data) {
            if(data == 'ok'){
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:订单价格计算失败。");
        });
    }

    //修改 pause 字段
    $scope.change_pause_field = function(id,field_name,order_id,store){
        var dom = document.querySelector('#'+field_name);
        var new_key = angular.element(dom).val();
        var station = $scope.this_station;
        
        $http.get('/fuck/common/change_order.php', {
            params:{
                change_info_field:id,
                station:station,
                store:store,
                field_name:field_name,
                order_id:order_id,
                new_key:new_key
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.play_price(order_id);    // 价格计算
                $scope.show_back_info();
                $scope.pause_modal(id,store);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:修改info单个字段失败。");
        });
    }

//新建一单
    // 检测店铺
    $scope.check_store = function(){

        $http.get('/fuck/systems/store_manage.php', {
            params:{
                get_station:$scope.new_store
            }
        }).success(function(data) {
            if(data == ''){
                $scope.new_store = '';
                $scope.error_info = '无此店铺。';
            }else{
                $scope.this_station = data;
                $scope.error_info = '';
            }

        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:平台店铺获取失败。");
        });
    }

    // 检测订单号
    $scope.check_order_id = function(){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                check_order_id:$scope.new_order_id,
                station:$scope.this_station,
                store:$scope.new_store
            }
        }).success(function(data) {
            if(data == '0'){
                $scope.new_order_id = '';
                $scope.error_info = '无此订单号。';
            }else{
                $scope.error_info = '';
            }
            
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:订单号检测失败。");
        });
    }

    // 检测商品代码
    $scope.check_goods_code = function(){
        $http.get('/fuck/common/check_order.php', {
            params:{
                check_goods_code:$scope.new_goods_code
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.error_info = '';
            }else{
                $scope.new_goods_code = '';
                $scope.error_info = '无此商品代码。';
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:商品代码检测失败。");
        });
    }
}]);