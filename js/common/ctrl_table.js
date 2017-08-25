var app=angular.module('myApp');
app.controller('tableCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout','$compile', function($rootScope,$scope,$state,$http,$log,$timeout,$compile){
    //查询所有店铺
    $scope.get_store = function(){
        $http.get('/fuck/systems/store_manage.php', {params:{get_store:'all'}
        }).success(function(data) {
            $scope.all_store = data;
        }).error(function(data) {
            alert("严重！店铺读取失败。");
            $log.info(data);
        });
    };
    $scope.get_store();

    // 销售额查看
    $scope.look_sell = function(){
        $scope.shadow('open','ss_make','正在生成图表');
        $http.get('/fuck/table/sell_table.php', {
            params:{
                look_sell:'get',
                s_date:$scope.s_date,
                e_date:$scope.e_date,
                sell_store:$scope.sell_store
            }
        }).success(function(data) {
            $log.info(data)
            $scope.sell_data = data.table;
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow

            //图表数据
            $scope.chart_box = true;
            $scope.x_line = data.labels.split(",");
            $scope.sum_total_money = data.sum_total_money.split(",");
            $scope.sum_ems_money = data.sum_ems_money.split(",");
            $scope.sum_bill = data.sum_bill.split(",");
            $scope.sum_point = data.sum_point.split(",");
            $scope.sum_cheap = data.sum_cheap.split(",");
            $scope.sum_tax = data.sum_tax.split(",");
            $scope.sum_buy_money = data.sum_buy_money.split(",");

        // 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById('main'));

        // 指定图表的配置项和数据
        var option = {
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            // color: ['#ca8622','#51616d','#c23531','#36a38b'],
            toolbox: {
                show: true,
                feature: {
                    dataView: {readOnly: false},
                    magicType: {type: ['line', 'bar']},
                    restore: {},
                    saveAsImage: {}
                }
            },
            dataZoom: [
                {
                    id: 'dataZoomX',
                    type: 'slider',
                    xAxisIndex: [0],
                    filterMode: 'filter'
                },
                {
                    id: 'dataZoomY',
                    type: 'slider',
                    yAxisIndex: [0],
                    filterMode: 'empty'
                }
            ],
            legend: {
                data: ['总购买金额','总运费','总手续费','总积分','总优惠券','总消费税','总金額']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis:  {
                type: 'category',
                data: $scope.x_line
            },
            yAxis: {
                type: 'value'
                
            },
            series: [
                {
                    name: '总购买金额',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_buy_money
                },
                {
                    name: '总运费',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_ems_money
                },
                {
                    name: '总手续费',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_bill
                },
                {
                    name: '总积分',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_point
                },
                {
                    name: '总优惠券',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_cheap
                },
                {
                    name: '总消费税',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_tax
                },
                {
                    name: '总金額',
                    type: 'line',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'top'
                        }
                    },
                    data: $scope.sum_total_money
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。

        myChart.setOption(option);
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:单品统计失败。");
        });
    }

    // 下载销售明细表
    $scope.sell_detail_table = function(){
        $scope.shadow('open','ss_read','正在导出');
        $http.get('/fuck/table/sell_table.php', {
            params:{
                sell_detail_table:'down',
                s_date:$scope.s_date,
                e_date:$scope.e_date
            }
        }).success(function(data) {
            $log.info(data)
            if(data == 'ok'){
                window.location="/down/sell_detail_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载售明细表失败。");
        });
    }

    // 下载出库表
    $scope.out_table = function(){
        $scope.shadow('open','ss_read','正在导出');
        $http.get('/fuck/table/out_table.php', {
            params:{
                out_table:$scope.table_type,
                s_date:$scope.s_date,
                e_date:$scope.e_date
            }
        }).success(function(data) {
            $log.info(data)
            if(data == 'ok'){
                window.location="/down/"+$scope.table_type+".xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载出库表失败。");
        });
    };

    // 单品统计
    $scope.look_one = function(){
        $scope.shadow('open','ss_make','正在生成图表');
        $http.get('/fuck/table/out_table.php', {
            params:{
                look_one:$scope.goods_code,
                s_date:$scope.s_date,
                e_date:$scope.e_date,
            }
        }).success(function(data) {
            $scope.one_data = data.table;
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
            //图表数据
            $scope.chart_box = true;
            $scope.x_line = data.labels.split(",");
            $scope.sum_out_num = data.sum_out_num.split(",");
            $scope.sum_pause_ch = data.sum_pause_ch.split(",");
            $scope.sum_pause_jp = data.sum_pause_jp.split(",");


        // 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById('main'));

        // 指定图表的配置项和数据
        var option = {
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            color: ['#51616d','#c23531','#36a38b'],
            toolbox: {
                show: true,
                feature: {
                    dataView: {readOnly: false},
                    magicType: {type: ['line', 'bar']},
                    restore: {},
                    saveAsImage: {}
                }
            },
            dataZoom: [
                {
                    id: 'dataZoomX',
                    type: 'slider',
                    xAxisIndex: [0],
                    filterMode: 'filter'
                }
                // ,
                // {
                //     id: 'dataZoomY',
                //     type: 'slider',
                //     yAxisIndex: [0],
                //     filterMode: 'empty'
                // }
            ],
            legend: {
                data: ['总出货数', '中国出货数','日本出货数']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis:  {
                type: 'category',
                data: $scope.x_line
            },
            yAxis: {
                type: 'value'
                
            },
            series: [
                {
                    name: '总出货数',
                    type: 'line',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'right'
                        }
                    },
                    data: $scope.sum_out_num
                },
                {
                    name: '中国出货数',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_pause_ch
                },
                {
                    name: '日本出货数',
                    type: 'bar',
                    stack: '分量',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside'
                        }
                    },
                    data: $scope.sum_pause_jp
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。

        myChart.setOption(option);
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:单品统计失败。");
        });
    }

	// 查看冻结表
	$scope.look_pause = function(){
		$scope.pause_table = false;
		$http.get('/fuck/table/pause_table.php', {
            params:{
                look_pause:'get'
            }
        }).success(function(data) {
            $scope.pause_table = data;
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:读取冻结表失败。");
        });
	};

	// 下载冻结表
	$scope.down_pause = function(){
        $scope.shadow('open','ss_read','正在导出');
		$http.get('/fuck/table/pause_table.php', {
            params:{
                down_pause:'down'
            }
        }).success(function(data) {
        	// $log.info(data)
            if(data == 'ok'){
            	window.location="/down/pause_table.xlsx";
            }
            $timeout(function(){$scope.shadow('close');},1000); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载冻结表失败。");
        });
	}

    // 商品代码检测
    $scope.tip_goods_code = function(){
        
        $http.get('/fuck/common/check_order.php', {
            params:{
                tip_goods_code:$scope.goods_code
            }
        }).success(function(data) {
            $scope.goods_codes = data;
            // $log.info(data)
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:商品代码检测失败。");
        });
    }

    // 选中商品代码
    $scope.click_code = function(e){
        $scope.goods_code = e;
        $scope.goods_codes = false;
    }


    
}])