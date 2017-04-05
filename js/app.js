var myApp = angular.module('myApp', ['ui.router','ui.bootstrap','ngAnimate','mgcrea.ngStrap','ionic','ngFileUpload','ngFlatDatepicker']);
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
    //亚马逊订单管理
        //获取订单
        .state('site.amazon_get_orders',{
            url: '/amazon_get_orders/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_get_orders.html',
                    controller: function($scope){
                        $scope.status.isopen3 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen1 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                    }
                }
            }
        })

        //订单发货
        .state('site.amazon_send_express',{
            url: '/amazon_send_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_send_express.html',
                    controller: function($scope){
                        $scope.status.isopen3 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen1 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                    }
                }
            }
        })

        //生成发货单
        .state('site.amazon_make_orders',{
            url: '/amazon_make_orders/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_make_orders.html',
                    controller: function($scope){
                        $scope.status.isopen3 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen1 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                    }
                }
            }
        })

        //导入物流单
        .state('site.amazon_import_express',{
            url: '/amazon_import_express/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_import_express.html',
                    controller: function($scope){
                        $scope.status.isopen3 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen1 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                    }
                }
            }
        })

        //单品发货
        .state('site.amazon_send_one',{
            url: '/amazon_send_one/{data}',
            views:{
                'show@site':{
                    templateUrl: 'tpls/amazon/amazon_send_one.html',
                    controller: function($scope){
                        $scope.status.isopen3 = false;
                        $scope.status.isopen2 = false;
                        $scope.status.isopen1 = true;
                        $scope.status.isopen4 = false;
                        $scope.status.isopen5 = false;
                    }
                }
            }
        })


    // //模板管理
    //     //sku字段
    //     .state('site.skufield',{
    //         url: '/skufield/{data}',
    //         params : { respond: '3'},
    //         views:{
    //             'show@site':{
    //                 templateUrl: 'tpls/field/skufield.html'
    //             }
    //         }
    //     })
    //     //通用字段
    //     .state('site.commonfield',{
    //         url: '/commonfield/{data}',
    //         params : { respond: '3'},
    //         views:{
    //             'show@site':{
    //                 templateUrl: 'tpls/field/commonfield.html'
    //             }
    //         }
    //     })
    //     //雅虎字段
    //     .state('site.yahoofield',{
    //         url: '/yahoofield/{data}',
    //         params : { respond: '3'},
    //         views:{
    //             'show@site':{
    //                 templateUrl: 'tpls/field/yahoofield.html'
    //             }
    //         }
    //     })
    //     //乐天字段
    //     .state('site.rakutenfield',{
    //         url: '/rakutenfield/{data}',
    //         views:{
    //             'show@site':{
    //                 templateUrl: 'tpls/field/rakutenfield.html'
    //             }
    //         }
    //     })
    //     //亚马逊字段
    //     .state('site.amazonfield',{
    //         url: '/amazonfield/{data}',
    //         views:{
    //             'show@site':{
    //                 templateUrl: 'tpls/field/amazonfield.html'
    //             }
    //         }
    //     })

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
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
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
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
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
                        $scope.status.isopen4 = true;
                        $scope.status.isopen5 = false;
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
                        $scope.status.isopen5 = true;
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
                        $scope.status.isopen5 = true;
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
                        $scope.status.isopen5 = true;
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
                        $scope.status.isopen5 = true;
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
                        $scope.status.isopen5 = true;
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
