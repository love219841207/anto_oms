var myFilters = angular.module('myApp');
// 求和
myFilters.filter('sum', function(){
    return function(data, key){
        if(typeof(data) === undefined || typeof(key) === undefined ) {
            return 0;
        }else{
            var sum = 0;
            i = data.length - 1;
            for(; i >= 0; i--) {
                sum += parseInt(data[i][key]);
            }

            return sum;
        }
        
    };
});
myFilters.filter('no_sum', function(){
    return function(data, key){
        if(typeof(data) === undefined || typeof(key) === undefined ) {
            return 0;
        }else{
            var sum = 0;
            i = data.length - 1;
            for(; i >= 0; i--) {
                sum = parseInt(data[i][key]);
            }

            return sum;
        }
        
    };
});

myFilters.filter('store', function(){
    return function(item){
        if(item == 'ULTRA光ヤフオク!店'){
            return '7883';
        }
        if(item == 'safety-eye'){
            return '7428';
        }
    };
});

myFilters.filter('replace_symbol', function(){
    return function(item){
        return item.replace(/,/g,' ◆ ');
    };
});

myFilters.filter('name_filter', function(){
    return function(item){
        return item.replace(/\[[^\)]*\]/g,""); 
    };
});

myFilters.filter('over_upload', function(){
    return function(item){
        if(item == 0){
            return '-';
        }
        if(item == 1){
            return '上传过';
        }
    };
});

myFilters.filter('over_mail', function(){
    return function(item){
        if(item == 0){
            return '-';
        }
        if(item == 'ing'){
            return '正在发送中...';
        }
        if(item == 1){
            return '已发送';
        }
    };
})
;
myFilters.filter('status', function(){
    return function(item){
        if(item == 0){
            return '已关闭';
        }
        if(item == 1){
            return '已开启';
        }
    };
});

myFilters.filter('is_pause', function(){
    return function(item){
        if(item == 0){
            return '未开始 >>';
        }
        if(item == 'pass'){
            return '已扣完。';
        }
        if(item == 'pause'){
            return '.. 正在冻结并押货';
        }
        if(item == 'back'){
            return '已退押。';
        }
    };
});

myFilters.filter('order_line', function(){
    return function(item){
        if(item == 0){
            return '无详单';
        }
        if(item == 1){
            return '待处理';
        }
        if(item == 2){
            return '已合单';
        }
        if(item == 3){
            return '冻结';
        }
        if(item == 5){
            return '待发货';
        }
        if(item == 6){
            return 'close';
        }
        if(item == '-1'){
            return '回收站';
        }
        if(item == '-2'){
            return '待支付';   // 待支付
        }
        if(item == '-3'){   // 冻结退单
            return '已退押';   
        }
        if(item == '-4'){   // 已出快递单退单
            return '已退单';
        }
        if(item == '-5'){   // 待发货退回
            return '已退库';
        }
        if(item == 9){
            return '保留';
        }
    };
});