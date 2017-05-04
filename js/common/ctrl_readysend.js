var app=angular.module('myApp');
app.controller('readysendCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
	$scope.init_list = function(){
		$scope.send_table = '';
	}

	$scope.search_field = '';

	//筛选查询字段改变
    $scope.change_search_field = function(){
        $scope.search_key = '';
    }
	// 读取ready_send表
    // $scope.ready_send_info = function(){
    //     $http.get('/fuck/common/ready_send.php', {
    //         params:{
    //             ready_send_info:'get'
    //         }
    //     }).success(function(data) {
    //         $scope.send_table = data;
    //     }).error(function(data) {
    //         alert("系统错误，请联系管理员。");
    //         $log.info("error:读取ready_send表失败。");
    //     });
    // }
    //查询用户分页数

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
            ready_send_count:'get',
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
            }

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
                $scope.selectOption=page

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
            }
            //下一页
            $scope.Next = function () {
                $scope.selectPage($scope.selPage + 1);
            };
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
        });         
    }
    //获取序列内容_分页查询
    $scope.to_page = function(page){
        $scope.loading_shadow('open'); //打开loading
        $scope.now_page = page;

        //计算分页开始值
        var start = (page - 1)*$scope.pageSize;

        //查询列表
        var post_data = {
            ready_send_data:'get',
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
    }

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
    }

    //筛选组确定按钮
    $scope.filter_bar_submit = function(){
        if($scope.search_field != '' && $scope.search_key == ''){
            $scope.plug_alert('danger','关键词不能为空。','fa fa-exclamation-triangle');
            return false;
        }
        
        $scope.init_list(); //初始化列表数据
        $scope.get_count();     //分配页码
        $scope.to_page('1');   //再次初始化数据
    }
}]);