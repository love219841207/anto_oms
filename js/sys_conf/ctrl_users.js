var app=angular.module('myApp');
app.controller('usersCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){

	//获取员工列表
	$scope.get_user_list = function(){
		$http.get('/fuck/systems/users_manage.php', {params:{get_user_list:'all'}
        }).success(function(data) {
            $scope.all_users = data;
        }).error(function(data) {
            alert("严重！员工列表读取失败。");
            $log.info(data);
        });
	}
	$scope.get_user_list();

	//新增员工
	$scope.add_user = function(){
		$http.get('/fuck/systems/users_manage.php', {params:{add_user:$scope.new_user}
        }).success(function(data) {
            if (data == 'ok') {
                $scope.new_user = '';
                $scope.plug_alert('success','添加完成，请在下面的列表配置员工权限。','fa fa-smile-o');
            	$scope.get_user_list();        	
            }else if(data == 'has'){
                $scope.plug_alert('warning','员工已存在。','fa fa-meh-o');
            }else{
            	$scope.plug_alert('danger','员工添加失败。','fa fa-ban');
            	$log.info(data);
            }
        }).error(function(data) {
            alert("严重！新增员工失败。");
            $log.info(data);
        });
	}

	//密码重置传值
    $scope.re_pwd_modal = function(u_num,u_name){
        $scope.re_pwd_u_num = u_num;
        $scope.re_pwd_u_name = u_name;
    }

    //执行重置
    $scope.re_pwd_user = function(){
    	$http.get('/fuck/systems/users_manage.php', {params:{re_pwd:$scope.re_pwd_u_num}
        }).success(function(data) {
            if(data == 'ok'){
            	$scope.plug_alert('success','重置完成。','fa fa-smile-o');
            }
        }).error(function(data) {
            alert("严重！重置员工密码失败。");
            $log.info(data);
        });
    }

    //删除传值
    $scope.del_user_modal = function(u_num,u_name){
    	$scope.del_u_num = u_num;
        $scope.del_u_name = u_name;
    }

    //执行删除
    $scope.del_user = function(){
    	$http.get('/fuck/systems/users_manage.php', {params:{del_user:$scope.del_u_num}
        }).success(function(data) {
            if(data == 'ok'){
            	$scope.plug_alert('success','删除完成。','fa fa-trash-o');
            	$scope.get_user_list();  
            }
        }).error(function(data) {
            alert("严重！删除员工失败。");
            $log.info(data);
        });
    }

    //员工店铺分配传值
    $scope.change_usaer_store_modal = function(station,u_num){
        $scope.cg_station = station;
        $scope.cg_u_num = u_num;
        //获取员工现有店铺及所有店铺
        $http.get('/fuck/systems/users_manage.php', {params:{get_store:u_num,station:station}
        }).success(function(data) {
            $scope.all_store = data;
        }).error(function(data) {
            alert("严重！获取员工店铺失败。");
            $log.info(data);
        });    
    }

    //店铺保存
    $scope.save_mystore = function(){
    	var my_store_conf = [];
    	angular.forEach($scope.all_store, function(value, index){
    		if(value.has == true){
    			my_store_conf.push(value.store_name);
    		}
    	})

        var post_data = {cg_store_conf:my_store_conf,cg_u_num:$scope.cg_u_num,cg_station:$scope.cg_station};
        $http.post('/fuck/systems/users_manage.php', post_data).success(function(data) {  
            if(data=='ok'){
                $scope.plug_alert('success','保存完成。','fa fa-save');
                $scope.get_user_list(); 
                var time=new Date().getTime();
                $state.go('site.users_manage',{data:time});
            }else{
                $scope.plug_alert('danger','员工店铺保存失败。','fa fa-ban');
            }
        }).error(function(data) {  
            alert("严重！员工店铺保存失败。");
            $log.info(data);
        });  
    }

    //全选
    $scope.check_all_store = function(){
        angular.forEach($scope.all_store, function(value, index){
            $scope.all_store[index].has = true;
        })
    }
    
    //全不选
    $scope.check_no_store = function(){
        angular.forEach($scope.all_store, function(value, index){
            $scope.all_store[index].has = false;
        })
    }

    //可发货
    $scope.can_send = function(u_num){
        $http.get('/fuck/systems/users_manage.php', {params:{can_send:u_num}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.get_user_list(); 
                // var time=new Date().getTime();
                // $state.go('site.users_manage',{data:time});
            }
        }).error(function(data) {
            alert("严重！获取修改可发货失败。");
            $log.info(data);
        });
    }
    //可售后
    $scope.can_repair = function(u_num){
        $http.get('/fuck/systems/users_manage.php', {params:{can_repair:u_num}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.get_user_list(); 
                // var time=new Date().getTime();
                // $state.go('site.users_manage',{data:time});
            }
        }).error(function(data) {
            alert("严重！获取修改可发货失败。");
            $log.info(data);
        });
    }
}])