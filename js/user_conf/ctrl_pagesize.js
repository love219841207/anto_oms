var app=angular.module('myApp');
app.controller('pagesizeCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){
	//更新page_size
    $scope.updte_pagesize = function(){
    	var time=new Date().getTime()
        $http.get('/fuck/user_conf/pagesize.php', {params:{update_pagesize:$scope.new_pagesize}
        }).success(function(data) {
            if(data == 'ok') {
                $scope.plug_alert('success','设置完成。','fa fa-smile-o');
                $scope.new_pagesize = '';
            }else{
            	$scope.plug_alert('danger','PageSize更新失败。','fa fa-ban');
            	$log.info(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:更新PageSize。");
        });
    }
}])