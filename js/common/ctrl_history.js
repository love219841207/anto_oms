var app=angular.module('myApp');
app.controller('historyCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
    $scope.search_field = '';
    $scope.change_express_panel = function(station,send_id,express_num,express_day){
        $scope.express_station = station;
        $scope.express_send_id = send_id;
        $scope.express_num = express_num;
        $scope.express_day = express_day;
    };

    // 修改保存
    $scope.save_change_express =function(){
        $http.get('/fuck/common/history_order.php', {
            params:{
                save_change_express:$scope.express_send_id,
                station:$scope.express_station,
                express_num:$scope.express_num,
                express_day:$scope.express_day
            }
        }).success(function(data) {
            // $log.info(data);
            if(data == 'ok'){
                $scope.plug_alert('success','修改完成。','fa fa-smile-o');
            }
            $scope.send_table ='';
            $scope.filter_bar_submit();
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("退单查询失败");
        });
    };

// 查看售后数据
    $scope.repair_info = function(order_id){
        $scope.redi = '';
        $scope.shadow('open','ss_read','正在读取 '+order_id+' 售后数据');

        $http.get('/fuck/common/history_order.php', {
            params:{
                repair_info:order_id
            }
        }).success(function(data) {
            $scope.redi = data;
            $timeout(function(){$scope.shadow('close');},0); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("退单查询失败");
        });
    };

// 一键转入售后
    $scope.onekey_repair = function(){
        $http.get('/fuck/common/history_order.php', {
            params:{
                onekey_repair:'go'
            }
        }).success(function(data) {
            if(data=='ok'){
                $scope.to_page(1);
            }else{
                $log.info(data);
                $scope.plug_alert('danger','转入失败。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("退单查询失败");
        });
    };

// 退单查询
    $scope.search_back_order = function(){
        $scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/common/history_order.php', {
            params:{
                search_back_order:$scope.search_key
            }
        }).success(function(data) {
            $scope.send_table = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            $scope.can_back();  // 查询是否可退单
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("退单查询失败");
        });
    };

// 可退单查询 按钮
    $scope.can_back = function(){
        $http.get('/fuck/common/history_order.php', {
            params:{
                can_back_order:$scope.search_key
            }
        }).success(function(data) {
            // $log.info(data);
            $scope.can_btn = data.back_status;
            $scope.back_station = data.station;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("可退单查询失败");
        });
    };

// 退单
    $scope.back_order = function(){
        $http.get('/fuck/common/history_order.php', {
            params:{
                back_order:$scope.search_key,
                back_station:$scope.back_station
            }
        }).success(function(data) {
            if(data=='ok'){
                $scope.search_back_order();
                $scope.can_back();
            }else{
                $scope.plug_alert('danger','退单失败！','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("退单失败");
        });
    };

//数据分页查询开始
	//筛选查询字段改变
    $scope.change_search_field = function(){
        $scope.search_key = '';
    };

	$http.get('/fuck/common/list_order.php', {
    	params:{
    		get_pagesize:'get'
    	}
    }).success(function(data) {
        $scope.pageSize = data;
    }).error(function(data) {
        alert("系统错误，请联系管理员。error:PageSize获取失败。");
        $log.info("error:PageSize获取失败。");
    });

    //查询总数
    $scope.get_count = function(e){     //分页组件

        var post_data = {
            history_count:'get',
            search_field:$scope.search_field,
            search_key:$scope.search_key
        };
        // $log.info(post_data);
        $http.post('/fuck/common/search_order.php', post_data).success(function(data) { 
            //数据获取总数
            $scope.all_num = data;
            //分页参数
            $scope.pages = Math.ceil($scope.all_num / $scope.pageSize); //分页数
            $scope.newPages = $scope.pages > 5 ? 5 : $scope.pages;
            $scope.pageList = [];
            $scope.pageOption = [];
            $scope.selPage = 1; //默认第一页
            //默认上一页不能点
            $scope.pre_overflow = true;

            //分页要repeat的数组
            for (var i = 0; i < $scope.newPages; i++) {
                $scope.pageList.push(i + 1);
                $scope.jumpList = $scope.pageList;
            }
            if($scope.pages > 5){
                $scope.pageList.push('... '+$scope.pages); 
                $scope.jumpList = $scope.pageList;
            }

            //分页option要的数组
            for (var i = 0; i < $scope.pages; i++) {
                $scope.pageOption.push(i + 1);
            }

            //跳页
            $scope.clickOption = function(){
                if($scope.selectOption==null){
                    return false;
                }
                if($scope.selectOption <'3'){
                    $scope.pageList = $scope.jumpList;
                    $scope.selectPage($scope.selectOption);
                }else{
                    $scope.selectPage($scope.selectOption);
                }
            };

            //打印当前选中页索引
            $scope.selectPage = function (page) {
                $scope.pre_overflow = false;
                $scope.next_overflow = false;

                //判断首尾页
                if(page=="1 ..."){
                    page=1;
                    $scope.get_count();
                }else if(page=="... "+$scope.pages){
                    page=$scope.pages;
                }

                //跳页响应
                $scope.selectOption=page;

                //提示到头了
                if(page < 2){
                    $scope.pre_overflow = true;
                }else if(page > $scope.pages-1){
                    $scope.next_overflow = true;
                }

                //不能小于1大于最大
                if(page<1){
                    return false;
                }else if(page > $scope.pages){
                    return false;
                }

                //最多显示分页数5 #mid状态
                if (page > 2) {
                    //因为只显示5个页数，大于2页开始分页转换
                    var newpageList = [];
                    for (var i = (page - 3) ; i < ((page + 2) > $scope.pages ? $scope.pages : (page + 2)) ; i++) {
                        newpageList.push(i + 1);
                    }
                    
                    //中间的
                    if(page > 3 && page <$scope.pages+1){
                        newpageList.unshift('1 ...');
                        newpageList.push('... '+$scope.pages);
                    }
                    //3
                    if(page == 3){
                        newpageList.push('... '+$scope.pages);
                    }
                    //p-3 末尾变
                    if(page == $scope.pages-3){
                        newpageList.pop();
                        newpageList.push($scope.pages);
                    }
                    //p-2 末尾移除
                    if(page > $scope.pages-3){
                        newpageList.pop();
                    }
                    
                    $scope.pageList = newpageList;     
                }
                if(page == 4){  //4开始进入mid状态
                    $scope.pageList.shift('1 ...');
                    $scope.pageList.unshift(1);
                }

                $scope.selPage = page;
                $scope.isActivePage(page);
                // console.log("选择的页：" + page);
                //获取第page页数据
                $scope.to_page(page);
            };
            //设置当前选中页样式
            $scope.isActivePage = function (page) {
                return $scope.selPage == page;
            };
            //上一页
            $scope.Previous = function () {
                $scope.selectPage($scope.selPage - 1);
            };
            //下一页
            $scope.Next = function () {
                $scope.selectPage($scope.selPage + 1);
            };
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
        });         
    };
    //获取序列内容_分页查询
    $scope.to_page = function(page){
        $scope.loading_shadow('open'); //打开loading
        $scope.now_page = page;

        //计算分页开始值
        var start = (page - 1)*$scope.pageSize;

        //查询列表
        var post_data = {
            history_data:'get',
            start:start,
            page_size:$scope.pageSize,
            search_field:$scope.search_field,
            search_key:$scope.search_key
        };
        // $log.info(post_data);
        $http.post('/fuck/common/search_order.php', post_data).success(function(data) {  
            $scope.send_table = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            // console.log(data);
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
        });           
    };

    //修改分页参数
    $scope.change_pageSize = function(){
        if($scope.change_size==null ){
            $scope.plug_alert('danger','警告，不能为空','fa fa-exclamation-triangle');
            return false;
        }else{
            $scope.pageSize = $scope.change_size;   //新的分页数
            $scope.get_count();     //分配页码
            $scope.to_page('1');   //再次初始化数据
        }
    };

    //筛选组确定按钮
    $scope.filter_bar_submit = function(){
        if($scope.search_field != '' && $scope.search_key == ''){
            $scope.plug_alert('danger','关键词不能为空。','fa fa-exclamation-triangle');
            return false;
        }
        
        $scope.get_count();     //分配页码
        $scope.to_page('1');   //再次初始化数据
    };
//数据分页查询结束

}]);