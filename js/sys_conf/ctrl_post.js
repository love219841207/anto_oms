var app=angular.module('myApp');
app.controller('postCtrl', ['$scope','$state','$http','$log', function($scope,$state,$http,$log){
	$scope.look_post = function(){
        $http.get('/fuck/import_post.php', {params:{look_post:'look'}
        }).success(function(data) {
            // console.log(data)
            $scope.post_response = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("日本邮编读取失败");
        });
    }
}])