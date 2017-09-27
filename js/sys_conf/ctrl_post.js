var app=angular.module('myApp');
// 由于命名失误，该文件为系统设置中的上传控制器
app.controller('postCtrl', ['$scope','$state','$http','$log','$timeout', function($scope,$state,$http,$log,$timeout){
    // 默认新增
    $scope.radioModel = 'add';

    // 查看 post
	$scope.look_post = function(){
        $http.get('/fuck/import_post.php', {params:{look_post:'look'}
        }).success(function(data) {
            // console.log(data)
            $scope.post_response = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("日本邮编读取失败");
        });
    };

    //  查看 amz_mail
    $scope.look_amz_mail = function(){
        $http.get('/fuck/import_mail_bian.php', {params:{look_amz_mail:'look'}
        }).success(function(data) {
            // console.log(data)
            $scope.post_response = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("查看 amz_mail 失败");
        });
    };

    // 下载 amz_mail

    $scope.down_amz_mail = function(){
        $scope.shadow('open','ss_make','正在生成 ..');

        var post_data = {down_amz_mail:'down_amz_mail'};

        $http.post('/fuck/import_mail_bian.php', post_data).success(function(data) {
            if(data == 'ok'){
                window.location='/down/mail_table.xlsx';
            }else{
                $log.info(data);
                $scope.plug_alert('danger','下载失败。','fa fa-ban');
            }
            $timeout(function(){$scope.shadow('close');},500); //关闭shadow
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:下载amzmail失败。");
        });
    };

    //  查看 true_sku
    $scope.look_true_sku = function(){
        $http.get('/fuck/import_true_sku.php', {params:{look_true_sku:'look'}
        }).success(function(data) {
            // console.log(data)
            $scope.post_response = data;
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("查看 true_sku 失败");
        });
    };

    // 下载 true_sku
    $scope.down_true_sku = function(){
        window.location='/uploads/true_sku.xlsx';
    };

    // 邮编替换
    $scope.replace_post = function(){

        $http.get('/fuck/import_post.php', {params:{replace_post:$scope.from_replace_key,to_post:$scope.to_replace_key}
        }).success(function(data) {
            // console.log(data)
            if(data == 'ok'){
                $scope.plug_alert('success','替换完成。','fa fa-smile-o');
            }else{
                alert(data);
            }
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("查看 true_sku 失败");
        });
    };
}]);