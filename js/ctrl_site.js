var app=angular.module('myApp');
//面板控制器
app.controller('siteCtrl', ['$rootScope','$scope','$state','$stateParams','$http','$log','$timeout','$ionicLoading', function($rootScope,$scope,$state,$stateParams,$http,$log,$timeout,$ionicLoading){
    $rootScope.bg = false;

    //设置日期格式
    $scope.datepickerConfig = {
        allowFuture: true,
        dateFormat: 'YYYY-MM-DD'
    };

    //删掉日历
    var jp_cal = document.querySelector('.cal_wrapper');
    angular.element(jp_cal).remove();

    //欢迎词
    if($stateParams.respond == 'hello'){
        //查询u_name 和 u_num
        $http.get('/fuck/login.php', {params:{u_name:"get"}
        }).success(function(data) {
            if(data=="logout"){
                $state.go('login',{respond:'timeout'});  //跳转到首页登录
            }else{
                $scope.u_name = data;
                $timeout(function(){
                    $scope.plug_alert('info','欢迎您，'+data,'fa fa-user-circle-o');
                },1000);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("u_name获取失败。");
        });
    }else{
        $http.get('/fuck/login.php', {params:{u_name:"get"}
        }).success(function(data) {
            if(data=="logout"){
                $state.go('login',{respond:'timeout'});  //跳转到首页登录
            }else{
                $scope.u_name = data;
                $timeout(function(){
                    $scope.plug_alert('info','已更新','fa fa-refresh fa-spin');
                },1000);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("u_name获取失败。");
        });
    }

    $http.get('/fuck/login.php', {params:{u_num:"get"}
    }).success(function(data) {
        $scope.u_num = data.u_num;
        $scope.ucan_send = data.can_send;
        if($scope.u_num < '1001'){  //系统设置权限 is_admin
            $scope.is_admin = true;
        }
    }).error(function(data) {
        alert("系统错误，请联系管理员。");
        $log.info("u_num获取失败。");
    });

    //查询员工侧栏开关
    $scope.side_bar_status = function(){
        $http.get('/fuck/login.php', {params:{side_bar_status:"get"}
        }).success(function(data) {
            var dom = document.querySelector('#show_box');
            angular.element(dom).removeClass('container-show1');
            angular.element(dom).removeClass('container-show2');
            if(data == '1'){
                angular.element(dom).addClass('container-show1');  //特效1
                $scope.side_bar_2 = false;
                $scope.side_bar_2_open = false;
                $scope.side_bar_1_open = true;
                $timeout(function(){
                    $scope.side_bar_1 = true;
                },200);
            }else if(data == '2'){
                angular.element(dom).addClass('container-show2');  //特效2
                $scope.side_bar_1 = false;
                $scope.side_bar_1_open = false;
                $scope.side_bar_2_open = true;
                $scope.side_bar_2 = true;
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("side_bar 切换失败");
        });
    }
    $scope.side_bar_status();

    //更新员工侧栏开关
    $scope.update_side_bar_status = function(){
        $http.get('/fuck/login.php', {params:{update_side_bar_status:"get"}
        }).success(function(data) {
            if(data == 'ok'){
               $scope.side_bar_status();
            }else{

            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("side_bar 切换失败");
        });
    }

}])

app.controller('topCtrl', ['$rootScope','$scope','$state','$stateParams','$http','$log','$timeout', function($rootScope,$scope,$state,$stateParams,$http,$log,$timeout){
    $rootScope.now_station = '';    //初始化员工选择的平台
    $rootScope.now_store_bar = '';  //初始化员工选择的店铺

    // 连接库存系统
    $scope.ping_repo = function(){
        $http.get('/fuck/ping_repo.php', {params:{ping_repo:"ping"}
        }).success(function(data) {
            if(data.repo_status == 1){
                $timeout(function(){
                    $scope.repo_status = true;
                },300);
            }else{
                $scope.repo_status = false;
            }
            
            $timeout(function(){
                $scope.repo_status = false;
                $scope.ping_repo();
            },10000);
            
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("连接repo失败。");
        });
    }
    $scope.ping_repo();

    //平台切换
    $scope.change_station = function(){
        $rootScope.now_station = $scope.now_station;
        $rootScope.now_store_bar = '';  //平台切换重置选择的店铺
        $scope.now_store_bar = '';  //平台切换重置选择的店铺
        if($scope.now_station == ''){

        }else{

            //获取员工该平台的店铺
            $http.get('/fuck/systems/users_manage.php', {params:{get_my_store:$scope.now_station}
            }).success(function(data) {
                $scope.now_store = data;
            }).error(function(data) {
                alert("严重！员工平台店铺获取失败。");
                $log.info(data);
            });
        }
        $state.go('site');
        $scope.status.isopen1 = false;
        $scope.status.isopen2 = false;
        $scope.status.isopen3 = false;
        $scope.status.isopen4 = false;
        $scope.status.isopen5 = false;
    }

    //店铺切换
    $scope.change_now_store = function(){
        $rootScope.now_store_bar = $scope.now_store_bar;
        $state.go('site');
        $scope.status.isopen1 = false;
        $scope.status.isopen2 = false;
        $scope.status.isopen3 = false;
        $scope.status.isopen4 = false;
        $scope.status.isopen5 = false;
    }

    //版本号查询
    $http.get('/fuck/topbar.php', {params:{get_version:"get"}
    }).success(function(data) {
        $scope.sys_version = data;
    }).error(function(data) {
        alert("系统错误，请联系管理员。");
        $log.info("版本号获取失败");
    });

    //退出
    $scope.logout = function(){
        $http.get('/fuck/login.php', {params:{logout:"bye"}
        }).success(function(data) {
            if(data == 'bye'){
                $state.go('login',{respond:'logout'});  //跳转到首页登录
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("系统登出失败。");
        });
    }
}]) 

app.controller('FileController', ['$rootScope','$scope','$state', 'Upload' , '$timeout','$http','$log', function($rootScope,$scope,$state,Upload,$timeout,$http,$log) {
    $scope.uploadImg = '';

    //初始化上传
    $rootScope.init_upload = function(){
        $scope.upload_store = '';
        $scope.upload_file = '';
        $scope.count_order = false;
    }

    //选择文件
    $scope.chose_file = function(){
        $scope.upload_ok = false;
        $scope.progress_bar = 0;
    }
    //提交，此处只分快递公司，不分中国、日本
    $scope.submit = function (file_name,file_type,station) {
        $scope.upload($scope.upload_file,file_name,file_type,station);
    };
    $scope.upload = function (file,file_name,file_type,station) {
        Upload.upload({
            url: '/fuck/uploads.php',
            data: {'file_name': file_name,'file_type':file_type},
            file: file //上传的文件
        }).progress(function (evt) {
            //进度条
            $scope.progress_bar = parseInt(100.0 * evt.loaded / evt.total);
        }).success(function (data, status, headers, config) {
            if(data == 'ok'){
                $timeout(function(){
                    $scope.upload_ok = true;
                    $scope.upload_file = '';
                    //保存数据
                    $scope.shadow('open','ss_write','正在保存数据');
                    if(station == 'amazon'){
                        $timeout(function(){$scope.amazon_import_file(file_name)},1000);    
                    }
                },1000);
            }
        }).error(function (data, status, headers, config) {
            //上传失败
            console.log('上传错误信息: ' + status);
        });
    };

    //亚马逊文件导入
    $scope.amazon_import_file = function(file_name){
        if(file_name == 'oms'){     //导入快递单
            $http.get('/fuck/amazon/amazon_import_express.php', {params:{import_file:file_name}
            }).success(function(data) {
                console.log(data)
                $scope.ems_response = data;
                if($scope.ems_response.status=='ok'){
                    $timeout(function(){$scope.shadow('close');},1000);
                }
            }).error(function(data) {
                alert("系统错误，请联系管理员。");
                $log.info(file_name+" 快递单导入失败");
            });
        }else if(file_name == 'post'){  //导入邮编
            console.log('update_post')
            $http.get('/fuck/import_post.php', {params:{import_file:file_name}
            }).success(function(data) {
                // console.log(data)
                $scope.post_response = data;
                if($scope.post_response.status=='ok'){
                    $timeout(function(){$scope.shadow('close');},1000);
                    $scope.plug_alert('success','数据导入完成。','fa fa-smile-o');
                }
            }).error(function(data) {
                alert("系统错误，请联系管理员。");
                $log.info(file_name+" 日本邮编导入失败");
            });
        }else if(file_name == 'amazon_add_list'){   //导入订单
            $http.get('/fuck/amazon/amazon_import_list.php', {params:{import_add_list:file_name,store:$scope.upload_store}
            }).success(function(data) {
                console.log(data)
                $scope.count_order = data.count_order;
                $scope.insert_count = data.insert_count;
                $scope.has_count = data.has_count;
                if(data.status == 'ok'){
                    $scope.plug_alert('success','数据导入完成。','fa fa-smile-o');
                    $timeout(function(){$scope.shadow('close');},1000);
                    // 刷新
                    var time=new Date().getTime();
                    $state.go('site.order',{data:time});
                }
            }).error(function(data) {
                alert("系统错误，请联系管理员。");
                $log.info(file_name+" 日本邮编导入失败");
            });
        }
        
    }
}]);

app.controller('logCtrl', ['$scope','$state','$stateParams','$http','$log','$timeout', function($scope,$state,$stateParams,$http,$log,$timeout){
    $scope.log_type = 'all';    //默认所有类型
    $scope.log_user = 'all';    //默认所有人
    $scope.log_s_date = '';    //默认日期
    $scope.log_e_date = '';    //默认日期

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

    $scope.log_page = 0;
    $scope.res_logs = [];
    $scope.load_logs = function(e,user){
        $scope.loading_shadow('open'); //打开loading

        if(user == ''){

        }else{
            $scope.log_user = user; //我的日志
        }
        
        if(e == 'search'){
            $scope.log_page = 0;
            $scope.res_logs = [];
        }

        if($scope.log_s_date == $scope.log_e_date && $scope.log_s_date != ''){
            $scope.plug_alert('danger','警告，日期区间不能相同。','fa fa-exclamation-triangle');
            return false;
        }else{
            if($scope.log_s_date == undefined){
                $scope.log_s_date = '';
            }
            if($scope.log_e_date == undefined){
                $scope.log_e_date = '';
            }
            $http.get('/fuck/systems/sys_log.php', {params:{log_type:$scope.log_type,log_s_date:$scope.log_s_date,log_e_date:$scope.log_e_date,log_user:$scope.log_user,log_page:$scope.log_page}
            }).success(function(data) {
                $timeout(function(){$scope.loading_shadow('close');},300); //关闭loading
                if(data == ''){
                    $scope.more_btn = false;
                    $scope.plug_alert('warning','所有数据加载完毕。','fa fa-file-code-o');
                }else{
                    $scope.res_logs.push(data);
                    $scope.more_btn = true;
                }
            }).error(function(data) {
                alert("严重！日志读取失败。");
                $log.info(data);
            });
        }
    }

    $scope.more_logs = function(e){
        $scope.log_page = $scope.log_page +1;

        if(e == 'sys'){
            $scope.load_logs(e,$scope.log_user);
        }
        if(e == 'my'){
            $scope.load_logs(e,$scope.log_user);
        }
    }
}]);