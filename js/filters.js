var myFilters = angular.module('myApp');

myFilters.filter('replace_symbol', function(){
    return function(item){
        return item.replace(/,/g,' ◆ ');
    }
});