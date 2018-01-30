app.controller('rakutenCtrl', ['$scope','$state','$http','$log','$timeout', function($scope,$state,$http,$log,$timeout){
	//赋日期
    var myDate = new Date();
    var today = myDate.toLocaleDateString().replace("/","-");
    today = today.replace("/","-");
    $scope.s_date = today;
    $scope.e_date = today;
    $scope.init_express = function(){
        $scope.express_response = false;
    };

    //全选
    $scope.check_all_item = function(){
        angular.forEach($scope.express_list, function(value, index){
            $scope.express_list[index].is_click = true;
        });
        $scope.cc_all = false;
        $scope.check_items();
    };
    $scope.cc_all = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item = function(){
        angular.forEach($scope.express_list, function(value, index){
            $scope.express_list[index].is_click = false;
        });
        $scope.cc_all = true;
        $scope.check_items();
    };

    //反选
    $scope.check_back_item = function(){
        angular.forEach($scope.express_list, function(value, index){
            if($scope.express_list[index].is_click == true){
                $scope.express_list[index].is_click = false;
            }else{
                $scope.express_list[index].is_click = true;
            }
        });
        $scope.check_items();
    };

    // 选择未上传状态
    $scope.check_no_up_item = function(){
        angular.forEach($scope.express_list, function(value, index){
            if($scope.express_list[index].over_upload == 0){
                $scope.express_list[index].is_click = true;
            }else{
                $scope.express_list[index].is_click = false;
            }
        });
        $scope.check_items();
    };

    // 选择未去信状态
    $scope.check_no_mail_item = function(){
        angular.forEach($scope.express_list, function(value, index){
            if($scope.express_list[index].over_mail == 0){
                $scope.express_list[index].is_click = true;
            }else{
                $scope.express_list[index].is_click = false;
            }
        });
        $scope.check_items();
    };

    // 选择配送方式
    $scope.check_send_method = function(){
        if($scope.c_send_method == ''){
            $scope.check_no_item();
        }else{
            angular.forEach($scope.express_list, function(value, index){
            if($scope.express_list[index].send_method == $scope.c_send_method){
                $scope.express_list[index].is_click = true;
            }else{
                $scope.express_list[index].is_click = false;
            }
            });
            $scope.check_items();
        }
        
    };

    //check_items 选择项
    $scope.check_items = function(){
        var my_checked = [];
        angular.forEach($scope.express_list, function(value, index){
            if($scope.express_list[index].is_click == true){
                my_checked.push("'"+$scope.express_list[index].rakuten_order_id+"'");
            }
        });
        $scope.my_checked = my_checked;
        $scope.my_checked_items = my_checked.join(',');
    };
    $scope.check_items();

	//	获取快递列表
	$scope.get_express_list = function(){
		$scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/rakuten/rakuten_send_express.php', {
            params:{
            	get_express_list:'get',
                store:$scope.now_store_bar,
                s_date:$scope.s_date,
                e_date:$scope.e_date
            }
        }).success(function(data) {
            $scope.express_list = data;
            $scope.check_no_item();
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取快递列表失败。");
        });
	};

	// 下载xlsx
	$scope.down_express_xlsx = function(){
        $scope.shadow('open','ss_read','正在导出');

        var post_data = {
            down_express_xlsx:'down',
            my_checked_items:$scope.my_checked_items
        };

        $http.post('/fuck/rakuten/rakuten_send_express.php', post_data).success(function(data) {
            if(data == 'ok'){
                window.location="/down/RkutenExpress.csv";
            }else{
                $log.info(data);
                $scope.plug_alert('danger','下载失败。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载上传快递单失败。");
        });
	};

    // 发送发货通知信
    $scope.send_over_mail = function(){
        $scope.shadow('open','ss_read','正在发送');

        var post_data = {
            send_mail:'rakuten',
            station:$scope.now_station,
            store:$scope.now_store_bar,
            mail_tpl:'send_express',
            my_checked_items:$scope.my_checked_items
        };

        $http.post('/fuck/mail/rakuten_send_mail.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','已经提交发信，点击【查看】刷新进度。','fa fa-smile-o');
                $scope.get_express_list();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载上传快递单失败。");
        });
    };

}]);