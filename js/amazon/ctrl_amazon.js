var app=angular.module('myApp');
app.controller('amazonCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){

// 00000000000000000000000000000000000000  亚马逊初始化开始 00000000000000000000000000000000000000
    //默认tool_bar关闭
    $scope.tool_1 = false;
    $scope.tool_2 = false;
    $scope.tool_3 = false;

    //order_line select默认值
    $scope.search_order_line = 'mark';

    //查询分页数
    $http.get('/fuck/amazon/amazon_get_order.php', {params:{get_pagesize:'get'}
    }).success(function(data) {
        $scope.pageSize = data;
    }).error(function(data) {
        alert("系统错误，请联系管理员。");
        $log.info("error:PageSize获取失败。");
    });
    
    //初始化数据view层
    $scope.init_list = function(){
        $scope.get_order_list_data = '';    //订单列表数据
        $scope.has_orders_data = '';    //同步重复列表数据
        $scope.common_order_data = '';   //合单列表数据
        $scope.count_order = '';    //同步订单数
        $scope.insert_count = '';   //插入订单数
        $scope.has_count = '';  //导入订单数
        $scope.post_pass = '';  //邮编通过数
        $scope.post_cut = '';   //邮编未通过数
        $scope.post_no = '';    //无邮编数
        $scope.syn_end = false; //同步关闭
    }

    //筛选组
    //初始化日期筛选
    $scope.init_date_bar = function(){
        $scope.search_date = '';
    }
    //初始化日期区间
    $scope.init_date = function(){
        $scope.s_date = '';
        $scope.e_date = '';
    }
    //初始化字段
    $scope.init_s_field = function(){
        $scope.search_field = '';
    }
    //初始化关键词
    $scope.init_s_key = function(){
        $scope.search_key = '';
    }

    $scope.init_filter_bar = function(){    //初始化筛选组
        $scope.init_date_bar(); //初始化筛选日期
        $scope.init_date(); //初始化日期区间
        $scope.init_s_field();  //初始化筛选字段
        $scope.init_s_key();    //初始化关键词
    }
    $scope.init_list(); //初始化列表数据
    $scope.init_filter_bar(); //初始化筛选组

    //筛选日期字段
    $scope.change_search_date = function(){
        $scope.init_date(); //初始化日期区间
    }

    //筛选查询字段
    $scope.change_search_field = function(){
        $scope.init_s_key();    //初始化关键词
    }

    //筛选组确定按钮
    $scope.filter_bar_submit = function(){
        if($scope.search_date != ''){
            if($scope.s_date == $scope.e_date){
                $scope.plug_alert('danger','警告，日期区间不能相同。','fa fa-exclamation-triangle');
                return false;
            }
        }
        if($scope.search_field != '' && $scope.search_key == ''){
            $scope.plug_alert('danger','关键词不能为空。','fa fa-exclamation-triangle');
            return false;
        }
        
        $scope.init_list(); //初始化列表数据
        $scope.get_count();     //分配页码
        $scope.to_page('1');   //再次初始化数据
    }
// 00000000000000000000000000000000000000  亚马逊初始化结束 00000000000000000000000000000000000000
// 1111111111111111111111111111111111111111  同步亚马逊订单开始 1111111111111111111111111111111111111111 

	//同步亚马逊订单列表
    $scope.list_orders = function(){
        $scope.init_list(); //初始化列表数据
        $scope.init_filter_bar(); //初始化筛选组

        $scope.shadow('open','ss_syn','正在同步 Amazon 订单列表');
        $http.get('/fuck/amazon/amazon_get_order.php', {params:{list_orders:$scope.now_store_bar,station:$scope.now_station}
        }).success(function(data) {
            if(data.status == 'list_ok'){
                $scope.shadow('open','ss_write','正在保存数据');
                $timeout(function(){$scope.has_orders($scope.now_store_bar)},1000);
            }
            $scope.count_order = data.count_order;
            $scope.insert_count = data.insert_count;
            $scope.has_count = data.has_count;
            $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:亚马逊ListOrders通信失败。");
        });
    }

    //判断订单是否重复
    $scope.has_orders = function(store){
        $scope.shadow('open','ss_read','判断重复数据');
        $http.get('/fuck/amazon/amazon_get_order.php', {params:{has_orders:store}
        }).success(function(data) {
            $scope.has_orders_data = data;
            $timeout(function(){$scope.get_order_info(store)},1500);
            // $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:判断重复数据失败。");
        });
    }

    //获取订单详单
    $scope.get_order_info = function(store){
        $scope.shadow('open','ss_syn','正在同步 Amazon 订单详单');
        $http.get('/fuck/amazon/amazon_get_order.php', {params:{get_order_info:store,station:$scope.now_station}
        }).success(function(data) {
            if(data.status == 'info_ok'){
                $scope.plug_alert('success','订单同步完毕。','fa fa-smile-o');
                $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
                $scope.need_check_num();    //获取需要验证的订单数
                $scope.syn_end = true;
            }else{
                $scope.plug_alert('warning','同步超时，请重试。','fa fa-clock-o');
                $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
            }
            $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:服务器响应亚马逊ListOrderItems同步订单详单通信失败。");
        });
    }

// 1111111111111111111111111111111111111111  同步亚马逊订单结束 1111111111111111111111111111111111111111 

// 2222222222222222222222222222222222222222  亚马逊订单验证开始 2222222222222222222222222222222222222222 

    // 获取需要验证订单数
    $scope.need_check_num = function(){
        $http.get('/fuck/common/check_order.php', {
            params:{
                need_check_num:$scope.now_store_bar
            }
        }).success(function(data) {
            $scope.need_check_number = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取需要验证订单数失败。");
        });
    }
    $scope.need_check_num();    //页面载入预读取

    // 点击验证按钮
    $scope.check_all_field = function(){
        $scope.shadow('open','ss_read','正在验证订单，请稍后');
        $http.get('/fuck/common/check_order.php', {
            params:{
                check_all_field:$scope.now_store_bar,
                station:$scope.now_station
            }
        }).success(function(data) {
            if(data == 'ok'){
                $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
                $scope.plug_alert('success','验证完成。','fa fa-smile-o');
                $scope.need_check_num();
            }else{
                $scope.plug_alert('danger','验证任务失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:验证订单失败。");
        });
    }

// 2222222222222222222222222222222222222222  亚马逊订单验证结束 2222222222222222222222222222222222222222 

// 3333333333333333333333333333333333333333  亚马逊列表展示开始 3333333333333333333333333333333333333333

    //查询总数
    $scope.get_count = function(e){     //分页组件
    $scope.list_order_count(); //统计标记和错误数

        var post_data = {
            items_count:$rootScope.now_store_bar,
            station:$scope.now_station,
            search_date:$scope.search_date,
            start_date:$scope.s_date,
            end_date:$scope.e_date,
            search_order_line:$scope.search_order_line,
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
        start = (page - 1)*$scope.pageSize;

        //查询列表
        var post_data = {
            get_order_list:$rootScope.now_store_bar,
            station:$scope.now_station,
            start:start,
            search_order_line:$scope.search_order_line,
            page_size:$scope.pageSize,
            search_date:$scope.search_date,
            start_date:$scope.s_date,
            end_date:$scope.e_date,
            search_field:$scope.search_field,
            search_key:$scope.search_key
        };
        // $log.info(post_data);
        $http.post('/fuck/common/search_order.php', post_data).success(function(data) {  
            $scope.get_order_list_data = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
            $timeout(function(){$scope.shadow('close');},300); //关闭shadow
            // console.log(data);
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
        });           
    }

    //修改search_order_line
    $scope.change_serch_line = function(){
        $scope.to_page(1);
        $scope.get_count();
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

    //展示订单列表
    $scope.only_list = function(){
        $scope.init_list(); //初始化列表数据
        $scope.init_filter_bar(); //初始化筛选组

        $scope.shadow('open','ss_read','正在加载订单列表');
        
        $scope.get_count();     //分配页码
        $scope.to_page('1');   //初始化数据
    };

    // 加载列表标记、统计错误数，在分页上显示
    $scope.list_order_count = function(){
        $http.get('/fuck/common/search_order.php', {params:{list_order_count:$scope.now_store_bar,station:$scope.now_station}
        }).success(function(data) {
            $scope.now_list_order_count = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:正在加载数据列表失败。");
        });
    };
// 3333333333333333333333333333333333333333  亚马逊列表展示结束 3333333333333333333333333333333333333333  

// 4444444444444444444444444444444444444444  查看修改列表字段开始   4444444444444444444444444444444444444444
    // 读取邮编地址
    $scope.read_oms_post = function(post_code){
        $http.get('/fuck/common/check_order.php', {
            params:{
                read_oms_post:post_code}
        }).success(function(data) {
            $scope.now_post_name = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取邮编地址失败。");
        });
    }

    // --- 搜索邮编查询开始 ---
    $scope.search_oms_post = function(){
        $scope.search_post_addr = ''; //清空另一个
        $http.get('/fuck/common/check_order.php', {
            params:{
                search_oms_post:$scope.search_post_code}
        }).success(function(data) {
            $scope.oms_post_addr_data = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:搜索邮编地址失败。");
        });
    }
    $scope.search_oms_addr = function(){
        $scope.search_post_code = '';   //清空另一个
        $http.get('/fuck/common/check_order.php', {
            params:{
                search_oms_addr:$scope.search_post_addr}
        }).success(function(data) {
            $scope.oms_post_addr_data = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:搜索邮编地址失败。");
        });
    }
    // --- 搜索邮编查询结束 ---

    //查看单个详情
    $scope.show_one_info = function(order_id){
        $scope.loading_shadow('open'); //打开loading
        // 初始化参考地域
        $scope.now_post_name = '';
        $http.get('/fuck/common/change_order.php', {
            params:{
                store:$scope.now_store_bar,
                station:$scope.now_station,
                show_one_info:order_id
            }
        }).success(function(data) {
            if(data.status=='ok'){
                $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading 
                $scope.one_res_list = data.res_list;
                $scope.one_res_info = data.res_info;
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:查看单个详情失败。");
        });
    }

    // 验证修改的list字段
    $scope.need_check_list = function(field_name,order_id){
        var dom = document.querySelector('#'+field_name);
        var new_key = angular.element(dom).val();

        $http.get('/fuck/common/check_order.php', {
            params:{
                need_check_list:$scope.now_store_bar,
                station:$scope.now_station,
                field_name:field_name,
                new_key:new_key,
                order_id:order_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.to_page($scope.now_page);
                $scope.change_list_field(field_name,order_id,new_key);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:验证list失败。");
        });
    }

    // 验证邮编、地址并修改
    $scope.change_post_addr = function(order_id){
        var dom = document.querySelector('#new_post_code');
        var new_post_code = angular.element(dom).val();
        var dom = document.querySelector('#new_address');
        var new_address = angular.element(dom).val();

        $http.get('/fuck/common/check_order.php', {
            params:{
                change_post_addr:$scope.now_store_bar,
                station:$scope.now_station,
                new_post_code:new_post_code,
                new_address:new_address,
                order_id:order_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.to_page($scope.now_page);
                $scope.show_one_info(order_id);
                $scope.plug_alert('success','通过。','fa fa-smile-o');
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:验证list失败。");
        });
    }

    // 修改list字段
    $scope.change_list_field = function(field_name,order_id,new_key){    //字段名，订单号
        $http.get('/fuck/common/change_order.php', {
            params:{
                change_list_field:'change',
                store:$scope.now_store_bar,
                station:$scope.now_station,
                order_id:order_id,
                field_name:field_name,
                new_key:new_key}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_one_info(order_id);
            }else{
                $scope.plug_alert('danger','修改失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:修改list单个字段失败。");
        });
    }

    //修改info字段
    $scope.change_info_field = function(id,field_name,index,amazon_order_id){
        var dom = document.querySelector('#'+field_name+'_'+index);
        var new_key = angular.element(dom).val();
        
        $http.get('/fuck/common/change_order.php', {
            params:{
                change_info_field:id,
                station:$scope.now_station,
                store:$scope.now_store_bar,
                field_name:field_name,
                order_id:amazon_order_id,
                new_key:new_key
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.show_one_info(amazon_order_id);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:修改info单个字段失败。");
        });
    }

    //验证电话号码格式
    $scope.check_phone = function(){
        $scope.pass_phone = false;
        var dom = document.querySelector('#phone');
        var phone = angular.element(dom).val();
        if(isNaN(phone)){
            if(phone.indexOf("-") > 0){
                phone = phone.replace(/-/g, "");
                angular.element(dom).val(phone);
                $scope.check_phone();
            }else{
                angular.element(dom).val('');
                $scope.pass_phone = false;
            }
        }else{
            var len = phone.length;
            if(len == 10){
                var a1 = phone.slice(0,2);
                var a2 = phone.slice(2,6);
                var a3 = phone.slice(6,10);
                phone = a1+"-"+a2+"-"+a3;
                angular.element(dom).val(phone);
                $scope.pass_phone = true;
            }if(len == 11){
                var a1 = phone.slice(0,3);
                var a2 = phone.slice(3,7);
                var a3 = phone.slice(7,11);
                phone = a1+"-"+a2+"-"+a3;
                angular.element(dom).val(phone);
                $scope.pass_phone = true;
            }if(len > 11){
                var a1 = phone.slice(0,3);
                var a2 = phone.slice(3,7);1
                var a3 = phone.slice(7,11);
                phone = a1+"-"+a2+"-"+a3;
                angular.element(dom).val(phone);
                $scope.pass_phone = true;
                $scope.plug_alert('danger','电话长度超过了。','fa fa-ban');
            }
        }   
    }
// 4444444444444444444444444444444444444444  查看修改列表字段结束   4444444444444444444444444444444444444444
    
// 5555555555555555555555555555555555555555  订单操作开始   5555555555555555555555555555555555555555
    //全选
    $scope.check_all_item = function(){
        angular.forEach($scope.get_order_list_data, function(value, index){
            $scope.get_order_list_data[index].is_click = true;
        })
        $scope.cc_all = false;
        $scope.check_items();
    }
    $scope.cc_all = true; //默认显示全选按钮
    
    //全不选
    $scope.check_no_item = function(){
        angular.forEach($scope.get_order_list_data, function(value, index){
            $scope.get_order_list_data[index].is_click = false;
        })
        $scope.cc_all = true;
        $scope.check_items();
    }

    //反选
    $scope.check_back_item = function(){
        angular.forEach($scope.get_order_list_data, function(value, index){
            if($scope.get_order_list_data[index].is_click == true){
                $scope.get_order_list_data[index].is_click = false;
            }else{
                $scope.get_order_list_data[index].is_click = true;
            }
        })
        $scope.check_items();
    }

    //check_items 选择项
    $scope.check_items = function(){
        var my_checked = new Array();
        angular.forEach($scope.get_order_list_data, function(value, index){
            if($scope.get_order_list_data[index].is_click == true){
                my_checked.push("'"+$scope.get_order_list_data[index].amazon_order_id+"'");
            }
        })
        $scope.my_checked = my_checked;
        $scope.my_checked_items = my_checked.join(',');
    }

    //备注按钮点击
    $scope.change_note = function(amazon_order_id){
        $scope.change_note_order_id = amazon_order_id;  //备注传值
        $scope.loading_shadow('open'); //打开loading
        //读取备注
        $http.get('/fuck/common/change_order.php', {
            params:{
                read_note:$scope.change_note_order_id,
                station:$scope.now_station
            }
        }).success(function(data) {
            $scope.change_note_key = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:备注读取失败。");
        });
    }

    //备注
    $scope.save_note = function(){
        $scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/common/change_order.php', {
            params:{
                change_note:$scope.change_note_order_id,
                note:$scope.change_note_key,
                station:$scope.now_station,
                store:$scope.now_store_bar
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','已保存。','fa fa-smile-o');
            }else{
                $scope.plug_alert('danger','保存失败。','fa fa-ban');
            }
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:备注保存失败。");
        });
    }

    // 标记订单
    $scope.mark_orders = function(e){
        $scope.check_items();   // 选择项

        $scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/common/change_order.php', {
            params:{
                mark_orders:$scope.my_checked_items,
                station:$scope.now_station,
                method:e
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.selectPage($scope.now_page);
                $scope.list_order_count();  //统计
            }else{
                 $scope.plug_alert('danger','操作失败。','fa fa-ban');
            }
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:订单标记失败。");
        });
    }

    // 删除订单
    $scope.amz_del_items = function(){
        $scope.shadow('open','ss_make','正在删除，请稍后。');

        var post_data = {del_items:$scope.my_checked_items,station:$scope.now_station,store:$scope.now_store_bar};

        $http.post('/fuck/common/change_order.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.get_count();
                $scope.to_page($scope.now_page);
                
                $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                $scope.plug_alert('success','删除完成。','fa fa-smile-o');
            }else{
                $log.info(data);
                $scope.plug_alert('danger','删除失败，请联系管理员。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除订单失败。");
        });
    }

    //还原订单
    $scope.amz_return =  function(){
        $scope.shadow('open','ss_make','正在还原，请稍后。');

        var post_data = {return_items:$scope.my_checked_items,station:$scope.now_station,store:$scope.now_store_bar};

        $http.post('/fuck/common/change_order.php', post_data).success(function(data) {
            if(data == 'ok'){
                $scope.get_count();
                $scope.to_page($scope.now_page);
                
                $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                $scope.plug_alert('success','还原完成。','fa fa-smile-o');
            }else{
                $log.info(data);
                $scope.plug_alert('danger','还原失败，请联系管理员。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:删除订单失败。");
        });
    }
// 5555555555555555555555555555555555555555  订单操作结束   5555555555555555555555555555555555555555

    // 分配快递公司
    $scope.express_company = function(){
        $scope.shadow('open','ss_write','正在分配配送公司');
        $http.get('/fuck/amazon/amazon_get_order.php', {
            params:{
                express_company:$scope.now_store_bar
            }
        }).success(function(data) {
            if(data == 'ok'){
                $timeout(function(){$scope.fu_bag();},2000);
            }else{
                $log.info(data);
                $scope.plug_alert('danger','快递公司分配失败。','fa fa-ban');
                $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:拆福袋失败。");
        });
    }





    //订单发货列表查询
    $scope.get_express_list = function(){
        $http.get('/fuck/amazon/amazon_send_express.php', {params:{amazon_express:'get'}
        }).success(function(data) {
            $scope.plug_alert('success','修改完成。','fa fa-smile-o');
            $scope.express_list = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:订单发货列表查询失败。");
        });
    }

    //订单发货
    $scope.send_express = function(){
        $scope.shadow('open','ss_syn','正在提交快递单号至 Amazon');
        $http.get('/fuck/amazon/amazon_send_express.php', {params:{send_express:'send'}
        }).success(function(data) {
            if(data=='ok'){
                $scope.plug_alert('success','已提交至亚马逊。','fa fa-smile-o');
                $scope.truncate_express();
                $timeout(function(){$scope.shadow('close');},1000);
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
            }
            $log.info(data);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:单品发货失败。");
        });
    }


    

    // 邮件模板查询
    $scope.get_mail_tpl = function(){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                read_mail_tpl:$rootScope.now_store_bar
            }
        }).success(function(data) {
            $scope.mail_tpls = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:邮件模板查询失败。");
        });
    }

    // 默认模板为空
    $scope.to_mail_tpl = '';

    // 读取邮件模板内容
    $scope.read_mail_info = function(){
        $log.info($scope.to_mail_tpl)
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                edit_mail_tpl:$scope.to_mail_tpl
            }
        }).success(function(data) {
            document.getElementById('mail_info_topic').innerHTML = data.mail_topic;
            document.getElementById('mail_info_html').innerHTML = data.mail_html;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:邮件内容读取失败。");
        });
    }

    // 发信
    $scope.amz_mail_items = function(){
        $scope.shadow('open','ss_make','正在发信，请稍后。');

        var post_data = {
            send_mail:'amazon',
            store:$rootScope.now_store_bar,
            station:'Amazon',
            mail_tpl:$scope.to_mail_tpl,
            order_items:$scope.my_checked_items};

        $http.post('/fuck/amazon_send_mail.php', post_data).success(function(data) {
            if(data.status == 'ok'){
                $scope.send_error_num = data.error_num;
                $scope.send_ok_num = data.ok_num;
                $timeout(function(){$scope.shadow('close');},500); //关闭shadow

                //读取错误信件info
                $scope.read_error_mail();
            }else{
                $log.info(data);
                $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:发信失败。");
        });
    }

    //读取错误邮件info
    $scope.read_error_mail = function(){
        $http.get('/fuck/amazon_send_mail.php', {
            params:{
                read_error_mail:'read'
            }
        }).success(function(data) {
            $scope.error_mail = data;
            $scope.plug_alert('success','发信完成。','fa fa-smile-o');
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:邮件内容读取失败。");
        });
    }

    

//合单
    //一键合单
    $scope.onekey_common_order = function(){
        $scope.shadow('open','ss_write','正在合单');
        $scope.init_list(); //初始化列表数据
        $http.get('/fuck/amazon/amazon_get_order.php', {
            params:{
                onekey_common_order:$rootScope.now_store_bar
            }
        }).success(function(data) {
            $scope.common_order_data = data;
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:一键合单失败。");
        });
    }

    //获取某个合单
    $scope.get_common_order = function(send_id){
        $scope.get_order_list_data = '';
        $scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/amazon/amazon_get_order.php', {
            params:{
                get_common_order:send_id
            }
        }).success(function(data) {
            $scope.get_order_list_data = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取某个合单失败。");
        });
    }

    //获取合单列表
    $scope.list_common_order = function(){
        $scope.init_list(); //初始化列表数据
        $scope.loading_shadow('open'); //打开loading
        $http.get('/fuck/amazon/amazon_get_order.php', {
            params:{
                list_common_order:$rootScope.now_store_bar
            }
        }).success(function(data) {
            $scope.common_order_data = data;
            $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取合单列表失败。");
        });
    }

    //拆单
    $scope.break_common_order = function(send_id){
        $scope.init_list(); //初始化列表数据
        $scope.shadow('open','ss_write','正在拆：'+send_id);
        $http.get('/fuck/amazon/amazon_get_order.php', {
            params:{
                break_common_order:send_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','拆单完成。','fa fa-smile-o');
                $scope.list_common_order();
            }else{
                $scope.plug_alert('danger','拆单失败。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:获取合单列表失败。");
        });
    }

    
}])