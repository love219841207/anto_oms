var myApp = angular.module('myApp', ['ui.router','ui.bootstrap','ngAnimate','mgcrea.ngStrap','ionic','ngFileUpload','ngFlatDatepicker','angular-drag']);
//由于整个应用都会和路由打交道，所以这里把$state和$stateParams这两个对象放到$rootScope上，方便其它地方引用和注入。
myApp.run(function($rootScope, $state, $stateParams) {
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;

    //跳转开始
    $rootScope.$on('$stateChangeStart',function(){ 
    	document.getElementById('info1').innerHTML='等待响应';
    	document.getElementById('info2').innerHTML='等待响应';
    })
    //跳转成功执行
    $rootScope.$on('$stateChangeSuccess',function(){ 
    	document.getElementById('info1').innerHTML='跳转完成！';
    })
    //跳转失败执行（可能没有找到模板）
    $rootScope.$on('$stateChangeError',function(){
    	document.getElementById('info1').innerHTML='跳转错误！';
    })
    //跳转没有找到（可能没有声明state）
    $rootScope.$on('$stateNotFound',function(){
    	document.getElementById('info1').innerHTML='跳转没有找到！';
    })

    //视图加载中
    $rootScope.$on('$viewContentLoading',function(){
    	document.getElementById('info2').innerHTML='视图...ing';
    });
    //视图加载完成
    $rootScope.$on('$viewContentLoaded',function(){
    	document.getElementById('info2').innerHTML='视图...ok';
    	
    });
});

//为post而设
myApp.config(function($httpProvider){
    $httpProvider.defaults.transformRequest=function(obj){
        var str=[];
        for(var p in obj){
            str.push(encodeURIComponent(p)+"="+encodeURIComponent(obj[p]));
        }
        return str.join("&");
    };
    $httpProvider.defaults.headers.post={'Content-Type':'application/x-www-form-urlencoded'}      
})

myApp.config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/login');	//重定向
    $stateProvider
    	.state('login', {
            url: '/login',
            data : { pageTitle: 'ANTO-OMS' },
            params : { respond: 'first'},
            templateUrl: 'tpls/login.html'
        })

        .state('site', {
            url: '/site',
            data : { pageTitle: 'ANTO-OMS 控制台' },
            params : { respond: '0'},
            views: {
                '': {
                    templateUrl: 'tpls/site.html'
                },
                'topbar@site': {
                    templateUrl: 'tpls/topbar.html'
                },
                'sidebar@site': {
                    templateUrl: 'tpls/sidebar.html'
                }
            }
        })

    //亚马逊
        // 亚马逊订单操作
        .state('site.amazon_order',{
            url: '/amazon_order/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/order.html',
                    controller: function($scope){
                        $scope.status.isopen1 = true;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        // 亚马逊上传快递单
        .state('site.amazon_syn_express',{
            url: '/amazon_syn_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_syn_express.html',
                    controller: function($scope){
                        $scope.status.isopen1 = true;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        // 邮件模板
        .state('site.mail_tpl',{
            url: '/mail_tpl/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/mail_tpl.html',
                    controller: function($scope){
                        $scope.status.isopen1 = true;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

    // 乐天
        // 乐天订单操作
        .state('site.rakuten_order',{
            url: '/rakuten_order/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/order.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = true;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        // 乐天上传快递单
        .state('site.rakuten_syn_express',{
            url: '/rakuten_syn_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_syn_express.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = true;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

    // 雅虎
        // 雅虎订单操作
        .state('site.yahoo_order',{
            url: '/yahoo_order/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/order.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        // 雅虎上传快递单
        .state('site.yahoo_syn_express',{
            url: '/yahoo_syn_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_syn_express.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

    //发货操作
        // 待出单
        .state('site.ready_send',{
            url: '/ready_send/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/ready_send.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        //生成发货单
        .state('site.make_send',{
            url: '/make_send/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/make_send.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        //已出单
        .state('site.close_send',{
            url: '/close_send/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/close_send.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        //导入快递单
        .state('site.import_express',{
            url: '/import_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/common/import_express.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })
    // 表格统计
        //冻结表
        .state('site.pause_table',{
            url: '/pause_table',
            views:{
                'show@site':{
                    templateUrl: 'tpls/table/pause_table.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = true;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })        

    //账号管理
        //PageSize
        .state('site.page_size',{
            url: '/page_size',
            views:{
                'show@site':{
                    templateUrl: 'tpls/user_conf/page_size.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = true;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        //密码修改
        .state('site.change_pwd',{
            url: '/change_pwd',
            views:{
                'show@site':{
                    templateUrl: 'tpls/user_conf/change_pwd.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = true;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })

        //我的日志
        .state('site.my_logs',{
            url: '/my_logs/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/user_conf/my_logs.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = true;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = false;
                    }
                }
            }
        })
        
    //系统设置
        //员工管理
        .state('site.users_manage',{
            url: '/users_manage/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/users_manage.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            }
        })

        //店铺管理
        .state('site.store_manage',{
            url: '/store_manage/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/store_manage.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            }
        })

        //运动代码管理
        .state('site.yf_code',{
            url: '/yf_code/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/yf_code.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            }
        })

        //邮编管理
        .state('site.sys_post',{
            url: '/sys_post/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/sys_post.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            }
        })

        //系统日志
        .state('site.sys_logs',{
            url: '/sys_logs/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/sys_logs.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            }
        })

        //系统版本
        .state('site.sys_version',{
            url: '/sys_version/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/sys_conf/sys_version.html',
                    controller: function($scope){
                        $scope.status.isopen1 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen3 = false;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                        $scope.status.isopen6 = false;
                        $scope.status.isopen7 = true;
                    }
                }
            },
            
        })

});

//页头
myApp.directive('title', ['$rootScope', '$timeout',
  function($rootScope, $timeout) {
    return {
      link: function() {

        var listener = function(event, toState) {

          $timeout(function() {
            $rootScope.title = (toState.data && toState.data.pageTitle) 
            ? toState.data.pageTitle 
            : '默认站名';	//默认title
          });
        };

        $rootScope.$on('$stateChangeSuccess', listener);
      }
    };
  }
]);
