var myFilters = angular.module('myApp');

myFilters.filter('replace_symbol', function(){
    return function(item){
        return item.replace(/,/g,' ◆ ');
    }
});

myFilters.filter('status', function(){
    return function(item){
        if(item == 0){
            return '已关闭';
        }
        if(item == 1){
            return '已开启';
        }
    }
})

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
    }
})

myFilters.filter('order_line', function(){
    return function(item){
    	if(item == 0){
    		return '无详单';
    	}
    	if(item == 1){
    		return '未通过';
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
    }
});