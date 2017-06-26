var app=angular.module('myApp');
app.controller('pauseorderCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
     $scope.radioModel = 'Left';
    //全选
    $scope.check_all_item = function(){
        angular.forEach($scope.all_pause_orders, function(value, index){
            $scope.all_pause_orders[index].is_click = true;
        })
        $scope.cc_all = false;
        $scope.check_items();
    }
    $scope.cc_all = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item = function(){
        angular.forEach($scope.all_pause_orders, function(value, index){
            $scope.all_pause_orders[index].is_click = false;
        })
        $scope.cc_all = true;
        $scope.check_items();
    }

    //反选
    $scope.check_back_item = function(){
        angular.forEach($scope.all_pause_orders, function(value, index){
            if($scope.all_pause_orders[index].is_click == true){
                $scope.all_pause_orders[index].is_click = false;
            }else{
                $scope.all_pause_orders[index].is_click = true;
            }
        })
        $scope.check_items();
    }

    //check_items 选择项
    $scope.check_items = function(){
        var my_checked = new Array();
        angular.forEach($scope.all_pause_orders, function(value, index){
            if($scope.all_pause_orders[index].is_click == true){
                my_checked.push("'"+$scope.all_pause_orders[index].order_id+"'");
            }
        })
        my_checked = unique1(my_checked);
        $scope.my_checked = my_checked;
        $scope.my_checked_items = my_checked.join(',');
        // $log.info($scope.my_checked_items)
    }
    $scope.check_items();

    // 数组去重
    function unique1(array){ 
        var n = []; //一个新的临时数组 
        //遍历当前数组 
        for(var i = 0; i < array.length; i++){ 
        //如果当前数组的第i已经保存进了临时数组，那么跳过， 
        //否则把当前项push到临时数组里面 
        if (n.indexOf(array[i]) == -1) n.push(array[i]); 
        } 
        return n; 
    } 

	// 查询所有平台冻结订单info表
    $scope.show_pause_info = function(){
        // $scope.all_pause_orders = '';
        $http.get('/fuck/common/pause_order.php', {
            params:{
                pause_order:'get'
            }
        }).success(function(data) {
            $scope.all_pause_orders = data;
            $scope.check_no_item();
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
    $scope.back_pause = function(id,store,station){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                station:station,
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
    $scope.to_pause = function(id,store,station){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                station:station,
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
    $scope.pause_modal = function(id,store,station){
        $scope.repo_num = '';
        // 查询单个详情
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                station:station,
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
    $scope.del_pause = function(id,store,station,order_id){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                store:store,
                station:station,
                del_pause:id,
                order_id:order_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.play_price(order_id);    // 价格计算
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
    $scope.change_pause_field = function(id,field_name,order_id,store,station){
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
                $scope.pause_modal(id,store,station);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:修改info单个字段失败。");
        });
    }

//新增一单
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

    // 新增一单
    $scope.new_pause_order = function(){
        $http.get('/fuck/common/pause_order.php', {
            params:{
                new_pause_order:$scope.new_order_id,
                station:$scope.this_station,
                store:$scope.new_store,
                new_goods_code:$scope.new_goods_code,
                new_goods_num:$scope.new_goods_num,
                new_unit_price:$scope.new_unit_price,
                new_yfcode:$scope.new_yfcode,
                new_cod_money:$scope.new_cod_money
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.play_price($scope.new_order_id);    // 价格计算
                $scope.show_pause_info();
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
            
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:订单号检测失败。");
        });
    }

    //扣库存
    $scope.sub_repo = function(){
        $scope.shadow('open','ss_write','正在扣库存，请稍后。');

        var post_data = {
            sub_repo:'get',
            station:'all_station',
            my_checked_items:$scope.my_checked_items
        };
        $http.post('/fuck/common/list_order.php', post_data).success(function(data) {  
            if(data=='ok'){
                $scope.show_pause_info();
                $scope.plug_alert('success','扣库完成。','fa fa-smile-o');
            }else{
                $log.info(data)
                $scope.plug_alert('danger','扣库失败。','fa fa-ban');
            }
            $scope.show_pause_info();
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
            $log.info("error:扣库存失败。");
        });  
    }

}]);