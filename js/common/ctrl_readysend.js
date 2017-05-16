var app=angular.module('myApp');
app.controller('readysendCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
	//初始化页面
	$scope.init_list = function(){
		// $scope.send_table = '';
	}
	$scope.search_field = '';
    $scope.send_table = '';

    //计算发货单
    $scope.repo_status = function(){
        $http.get('/fuck/common/ready_send.php', {
            params:{
                repo_status:'go'
            }
        }).success(function(data) {
            $scope.to_page($scope.now_page);
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:计算发货单。");
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

    //查询send详情
    $scope.show_send_info = function(id){
        $scope.sku_pass = false;
        $scope.repo_num = '';
        $http.get('/fuck/common/ready_send.php', {
            params:{
                show_send_info:id
            }
        }).success(function(data) {
            // $log.info(data)
            $scope.send_table_info = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取show_send_info失败。");
        });
    }

    // 重置express
    $scope.reset_express = function(){
        $scope.send_table = '';
        $http.get('/fuck/common/ready_send.php', {
            params:{
                reset_express:'reset'
            }
        }).success(function(data) {
            if(data == 'ok'){

            }else{
                $scope.plug_alert('danger','严重！重置快递失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:重置快递失败。");
        });
    }

    //打包
    $scope.packing = function(){
        $scope.send_table = '';
        $http.get('/fuck/common/ready_send.php', {
            params:{
                packing:'pack'
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','打包完毕。','fa fa-gift');
                $scope.to_page($scope.now_page);    //刷新列表
            }else{
                $scope.plug_alert('danger','打包失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:打包失败。");
        });
    }

    // 检测商品代码
    $scope.check_goods_code = function(){
        var dom = document.querySelector('#add_goods_code');
        var add_goods_code = angular.element(dom).val();
        $http.get('/fuck/common/check_order.php', {
            params:{
                check_goods_code:add_goods_code
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.sku_pass = true;
                $http.get('/fuck/common/ready_send.php', {
                    params:{
                        check_repo:add_goods_code
                    }
                }).success(function(data) {
                    $scope.add_repo = data['repo'];
                    $scope.add_a_repo = data['a_repo'];
                    $scope.add_b_repo = data['b_repo'];
                }).error(function(data) {
                    alert("系统错误，请联系管理员。");
                    $log.info("error:查看发货库存数失败。");
                });
            }else{
                $scope.sku_pass = false;
                angular.element(dom).val('');   //清空
                $scope.plug_alert('danger','无此商品代码。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:商品代码检测失败。");
        });
    }

    // 检测正数
    $scope.check_int = function(e){
        var dom = document.querySelector('#'+e);
        var num = angular.element(dom).val();
        if(num < 0 || num == ''){
            angular.element(dom).val('');
            $scope.plug_alert('danger','请输入大于 0 的数。','fa fa-ban');
            $scope[e] = false;
        }else{
            $scope[e] = true;

            // 如果是数量，检测是否有库存
            if(e == 'add_goods_num'){
                if($scope.sku_pass == false){
                    $scope.plug_alert('danger','请输入商品代码。','fa fa-ban');
                }else{
                    // 检测数量
                    if(num - $scope.add_repo > 0){
                        $scope.plug_alert('danger','库存不足。','fa fa-ban');
                        angular.element(dom).val('');
                    }
                }
            }
        }
    }

    // 添加发货item
    $scope.add_send_item = function(id){
        var dom = document.querySelector('#add_goods_code');
        var add_goods_code = angular.element(dom).val();
        var dom = document.querySelector('#add_goods_num');
        var add_goods_num = angular.element(dom).val();
        var dom = document.querySelector('#add_item_price');
        var add_item_price = angular.element(dom).val();
        var dom = document.querySelector('#add_yfcode');
        var add_yfcode = angular.element(dom).val();
        var dom = document.querySelector('#add_cod_money');
        var add_cod_money = angular.element(dom).val();

        var post_data = {
                add_send_item:add_goods_code,
                id:id,
                add_goods_num:add_goods_num,
                add_item_price:add_item_price,
                add_yfcode:add_yfcode,
                add_cod_money:add_cod_money
            };

        $http.post('/fuck/common/ready_send.php', post_data).success(function(data) {
            if(data.status == 'ok'){
                $scope.reset_express(); //重置快递
                $scope.play_price();    // 价格计算
                $scope.repo_status();   //计算发货单
            }else{
                $log.info(data);
                $scope.plug_alert('danger','添加项目失败，请联系管理员。','fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:添加item失败。");
        });
    }

    // 删除发货item
    $scope.del_send_item = function(id,oms_id,info_id,station,store){
         $http.get('/fuck/common/ready_send.php', {
            params:{
                del_send_item:id,
                oms_id:oms_id,
                info_id:info_id,
                station:station,
                store:store
            }
        }).success(function(data) {
            if(data.status == 'ok'){
                $scope.plug_alert('success','删除完成。','fa fa-smile-o');
                $scope.play_price();    // 价格计算
                $scope.repo_status();   //计算发货单
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取show_send_info失败。");
        });
    }

    //验证电话号码格式
    $scope.check_phone = function(){
        $scope.pass_phone = false;
        var dom = document.querySelector('#who_tel');
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

    // 验证修改的send字段
    $scope.need_check_send = function(field_name,o_key,station,store,id,oms_id,info_id){
        var dom = document.querySelector('#'+field_name);
        var new_key = angular.element(dom).val();

        $http.get('/fuck/common/ready_send.php', {
            params:{
                need_check_send:'get',
                station:station,
                store:store,
                field_name:field_name,
                new_key:new_key,
                o_key:o_key,
                id:id,
                oms_id:oms_id,
                info_id:info_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.change_send_field(o_key,station,store,field_name,new_key,id,oms_id,info_id);
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:验证send字段失败。");
        });
    }

    // 修改send字段
    $scope.change_send_field = function(o_key,station,store,field_name,new_key,id,oms_id,info_id){
        $http.get('/fuck/common/ready_send.php', {
            params:{
                change_send_field:'change',
                station:station,
                store:store,
                field_name:field_name,
                new_key:new_key,
                o_key:o_key,
                id:id,
                oms_id:oms_id,
                info_id:info_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.reset_express(); //重置快递
                $scope.show_send_info(id);
                $scope.repo_status();   //计算发货单
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:修改send字段失败。");
        });
    }

    // 验证邮编、地址并修改
    $scope.change_post_addr = function(station,store,id,oms_id,info_id){
        var dom = document.querySelector('#new_post_code');
        var new_post_code = angular.element(dom).val();
        var dom = document.querySelector('#new_address');
        var new_address = angular.element(dom).val();

        $http.get('/fuck/common/ready_send.php', {
            params:{
                change_post_addr:'change',
                station:station,
                store:store,
                new_post_code:new_post_code,
                new_address:new_address,
                id:id,
                oms_id:oms_id,
                info_id:info_id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.reset_express(); //重置快递
                $scope.to_page($scope.now_page);
                $scope.show_send_info(id);
                $scope.plug_alert('success','通过。','fa fa-smile-o');
            }else{
                $scope.plug_alert('danger',data,'fa fa-ban');
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:验证send邮编地址失败。");
        });
    }

//数据分页查询开始
	//筛选查询字段改变
    $scope.change_search_field = function(){
        $scope.search_key = '';
    }

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
//数据分页查询结束

}]);