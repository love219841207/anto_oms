var app=angular.module('myApp');
app.controller('storeCtrl', ['$rootScope','$scope','$state','$http','$log','$timeout', function($rootScope,$scope,$state,$http,$log,$timeout){
    //调用下拉
    $scope.plug_dropdown();

    //发货通知信开关
    $scope.toggle_send = function(station,store){
        $http.get('/fuck/systems/store_manage.php', {params:{toggle_send:store,station:station}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.over_send(station,store);
            }else{
                $scope.plug_alert('danger','操作失败！','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("发货通知信开关失败。");
            $log.info(data);
        });
    };

    // 获取发货通知信
    $scope.over_send = function(station,store){
        $http.get('/fuck/systems/store_manage.php', {params:{over_send:store,station:station}
        }).success(function(data) {
            $scope.toggle_btn = data;
        }).error(function(data) {
            alert("获取发货通知信失败。");
            $log.info(data);
        });
    };
    $scope.over_send($scope.now_station,$scope.now_store_bar);

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

    //删除传值
    $scope.del_store_modal = function(store_id,store_name){
        $scope.del_store_id = store_id;
        $scope.del_store_name = store_name;
    };

    //删除店铺
    $scope.del_store = function(){
        $http.get('/fuck/systems/store_manage.php', {params:{del_store:$scope.del_store_name}
        }).success(function(data) {
            if(data == 'ok'){
                $scope.plug_alert('success','已删除。','fa fa-trash-o');
                $scope.get_store();
            }
        }).error(function(data) {
            alert("严重！删除店铺失败。");
            $log.info(data);
        });
    };

	//新增店铺
    $scope.add_store = function(){
        $http.get('/fuck/systems/store_manage.php', {params:{new_store:$scope.new_store,station:$scope.click_key}
        }).success(function(data) {
            if (data == 'ok') {
                $scope.new_store = '';
                $scope.plug_alert('success','添加完成，请在下面的列表配置店铺参数。','fa fa-smile-o');
            	$scope.get_store();        	
            }else if(data == 'has'){
                $scope.plug_alert('warning','店铺已存在。','fa fa-meh-o');
            }else{
            	$scope.plug_alert('danger','店铺添加失败。','fa fa-ban');
            	$log.info(data);
            }
        }).error(function(data) {
            alert("严重！新增店铺失败。");
            $log.info(data);
        });
    };

    //店铺配置传值 & 获取配置详情
    $scope.conf_store_modal = function(station,store_name){
        $scope.conf_store_modal_title = store_name;
        $scope.conf_store_station = station;
        $http.get('/fuck/systems/store_manage.php', {params:{get_conf:station,store_name:store_name}
        }).success(function(data) {
            if(station == 'Amazon'){
                $scope.awsaccesskeyid = data.awsaccesskeyid;
                $scope.sellerid = data.sellerid;
                $scope.signatureversion = data.signatureversion;
                $scope.secret = data.secret;
                $scope.marketplaceid_id_1 = data.marketplaceid_id_1;
                $scope.mail_name = data.mail_name;
                $scope.mail_id = data.mail_id;
                $scope.mail_pwd = data.mail_pwd;
                $scope.mail_smtp = data.mail_smtp;
                $scope.mail_port = data.mail_port;
                $scope.mail_answer_addr = data.mail_answer_addr;
                $scope.mail_over_send = data.mail_over_send;
                $scope.use_yfcode = data.use_yfcode;
            }
            if(station == 'Yahoo'){
                $scope.mail_over_send = data.mail_over_send;
                $scope.use_yfcode = data.use_yfcode;
                $scope.mail_name = data.mail_name;
                $scope.mail_id = data.mail_id;
                $scope.mail_pwd = data.mail_pwd;
                $scope.mail_smtp = data.mail_smtp;
                $scope.mail_port = data.mail_port;
                $scope.mail_answer_addr = data.mail_answer_addr;
            }
            if(station == 'Rakuten'){
                $scope.mail_over_send = data.mail_over_send;
                $scope.use_yfcode = data.use_yfcode;
                $scope.mail_name = data.mail_name;
                $scope.mail_id = data.mail_id;
                $scope.mail_pwd = data.mail_pwd;
                $scope.mail_smtp = data.mail_smtp;
                $scope.mail_port = data.mail_port;
                $scope.mail_answer_addr = data.mail_answer_addr;
            }
            if(station == 'P_Yahoo'){
                $scope.mail_over_send = data.mail_over_send;
                $scope.use_yfcode = data.use_yfcode;
                $scope.mail_name = data.mail_name;
                $scope.mail_id = data.mail_id;
                $scope.mail_pwd = data.mail_pwd;
                $scope.mail_smtp = data.mail_smtp;
                $scope.mail_port = data.mail_port;
                $scope.mail_answer_addr = data.mail_answer_addr;
            }
        }).error(function(data) {
            alert("严重！店铺配置读取失败。");
            $log.info(data);
        });
    };

    //保存亚马逊店铺配置
    $scope.save_amazone_conf = function(){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                update_conf:$scope.conf_store_station,
                store_name:$scope.conf_store_modal_title,
                awsaccesskeyid:$scope.awsaccesskeyid,
                sellerid:$scope.sellerid,
                signatureversion:$scope.signatureversion,
                secret:$scope.secret,
                marketplaceid_id_1:$scope.marketplaceid_id_1,
                mail_name:$scope.mail_name,
                mail_id:$scope.mail_id,
                mail_pwd:$scope.mail_pwd,
                mail_smtp:$scope.mail_smtp,
                mail_port:$scope.mail_port,
                mail_answer_addr:$scope.mail_answer_addr,
                mail_over_send:$scope.mail_over_send,
                use_yfcode:$scope.use_yfcode
            }
        }).success(function(data) {
            if(data == 'ok'){}else{
                $scope.plug_alert('danger','保存失败。','fa fa-ban');
                $log.info(data);
            }
            // $log.info(data)
        }).error(function(data) {
            alert("严重！保存店铺配置失败。");
            $log.info(data);
        });
    };

    // 保存雅虎店铺配置
    $scope.save_yahoo_conf = function(){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                update_conf:$scope.conf_store_station,
                store_name:$scope.conf_store_modal_title,
                mail_name:$scope.mail_name,
                mail_id:$scope.mail_id,
                mail_pwd:$scope.mail_pwd,
                mail_smtp:$scope.mail_smtp,
                mail_port:$scope.mail_port,
                mail_answer_addr:$scope.mail_answer_addr,
                mail_over_send:$scope.mail_over_send,
                use_yfcode:$scope.use_yfcode
            }
        }).success(function(data) {
            $log.info(data);
        }).error(function(data) {
            alert("严重！保存店铺配置失败。");
            $log.info(data);
        });
    };
    
    // 保存乐天店铺配置
    $scope.save_rakuten_conf = function(){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                update_conf:$scope.conf_store_station,
                store_name:$scope.conf_store_modal_title,
                mail_name:$scope.mail_name,
                mail_id:$scope.mail_id,
                mail_pwd:$scope.mail_pwd,
                mail_smtp:$scope.mail_smtp,
                mail_port:$scope.mail_port,
                mail_answer_addr:$scope.mail_answer_addr,
                mail_over_send:$scope.mail_over_send,
                use_yfcode:$scope.use_yfcode
            }
        }).success(function(data) {
            $log.info(data);
        }).error(function(data) {
            alert("严重！保存店铺配置失败。");
            $log.info(data);
        });
    };

    // 保存拍卖店铺配置
    $scope.save_P_Yahoo_conf = function(){
        $http.get('/fuck/systems/store_manage.php', {
            params:{
                update_conf:$scope.conf_store_station,
                store_name:$scope.conf_store_modal_title,
                mail_name:$scope.mail_name,
                mail_id:$scope.mail_id,
                mail_pwd:$scope.mail_pwd,
                mail_smtp:$scope.mail_smtp,
                mail_port:$scope.mail_port,
                mail_answer_addr:$scope.mail_answer_addr,
                mail_over_send:$scope.mail_over_send,
                use_yfcode:$scope.use_yfcode
            }
        }).success(function(data) {
            $log.info(data);
        }).error(function(data) {
            alert("严重！保存店铺配置失败。");
            $log.info(data);
        });
    };

    //店铺发货通知信传值 & 详情
    $scope.express_mail_modal = function(station,store_name){
        $scope.express_mail_modal_station = station;
        $scope.express_mail_modal_store = store_name;
        $http.get('/fuck/systems/store_manage.php', {params:{get_express_mail:store_name}
        }).success(function(data) {
            $log.info(data);
            $scope.express_mail_modal_topic = data.mail_topic;
            $scope.express_mail_html = data.mail_html;
            $scope.express_mail_txt = data.mail_txt;
        }).error(function(data) {
            alert("严重！邮件模板获取失败。");
            $log.info(data);
        });
    };

    //保存发货通知信
    $scope.save_express_mail = function(){
        var post_data = {
            save_express_mail:$scope.express_mail_modal_store,
            mail_topic:$scope.express_mail_modal_topic,
            express_mail_html:$scope.express_mail_html,
            express_mail_txt:$scope.express_mail_txt
        };

        $http.post('/fuck/systems/store_manage.php', post_data).success(function(data) {  
            if(data == 'ok'){
                $scope.plug_alert('success','已保存。','fa fa-smile-o');
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {  
            alert('系统错误，请联系管理员。');
            $log.info('保存发货通知信失败。');
        });  
    };

    //店铺信件模板 & 详情
    $scope.mail_tpl = function(station,store_name){
        $scope.mail_tpl_station = station;
        $scope.mail_tpl_store = store_name;
        $scope.read_mail_tpl();
    };

    // 点击添加信件变量
    $scope.add_var = function(e){
        $scope.express_mail_html = $scope.express_mail_html + e;
        // $scope.express_mail_txt = $scope.express_mail_txt + e;
    };
    $scope.add_var2 = function(e){
        $scope.mail_tpl_html = $scope.mail_tpl_html + e;
        // $scope.mail_tpl_txt = $scope.mail_tpl_txt + e;
    };

    //新增店铺邮件模板
    $scope.add_mail_tpl = function(){
        $http.get('/fuck/systems/store_manage.php', {params:{
                add_mail_tpl:$scope.new_tpl,
                mail_tpl_store:$scope.mail_tpl_store
            }
        }).success(function(data) {
            $scope.read_mail_tpl();
        }).error(function(data) {
            alert("严重！新增店铺邮件模板失败。");
            $log.info(data);
        });
    };

    //读取店铺邮件模板
    $scope.read_mail_tpl = function(){
        $http.get('/fuck/systems/store_manage.php', {params:{
                read_mail_tpl:$scope.mail_tpl_store
            }
        }).success(function(data) {
            $scope.read_store_model = data;
        }).error(function(data) {
            alert("严重！新增店铺邮件模板失败。");
            $log.info(data);
        });
    };

    //邮件模板重命名
    $scope.rename_mail_tpl = function(id){
        var dom = document.querySelector('#rename_'+id);
        var new_name = angular.element(dom).val();
        $http.get('/fuck/systems/store_manage.php', {params:{
                rename_mail_tpl:id,
                new_name:new_name
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.read_mail_tpl();
            }else{
                $scope.plug_alert('danger','重命名失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("严重！邮件模板重命名失败。");
            $log.info(data);
        });
    };

    //删除邮件模板
    $scope.del_mail_tpl = function(id){
        $http.get('/fuck/systems/store_manage.php', {params:{
                del_mail_tpl:id
            }
        }).success(function(data) {
            if(data == 'ok'){
                $scope.read_mail_tpl();
            }else{
                $scope.plug_alert('danger','删除失败。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {
            alert("严重！删除邮件模板失败。");
            $log.info(data);
        });
    };

    //编辑邮件模板
    $scope.edit_tpl = function(id){
        $scope.edit_tpl_id = id;
        $scope.show_edit = true;
        $http.get('/fuck/systems/store_manage.php', {params:{
                edit_mail_tpl:id
            }
        }).success(function(data) {
            $scope.mail_tpl_topic = data.mail_topic;
            $scope.mail_tpl_html = data.mail_html;
            $scope.mail_tpl_txt = data.mail_txt;
        }).error(function(data) {
            alert("严重！编辑邮件模板读取失败。");
            $log.info(data);
        });
    };

    //保存邮件模板
    $scope.save_mail_tpl = function(){
        var post_data = {   
            save_mail_tpl:$scope.edit_tpl_id,
            mail_topic:$scope.mail_tpl_topic,
            mail_html:$scope.mail_tpl_html,
            mail_txt:$scope.mail_tpl_txt
        };

        $http.post('/fuck/systems/store_manage.php', post_data).success(function(data) {  
            if(data == 'ok'){
                $scope.plug_alert('success','已保存。','fa fa-smile-o');
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {  
            alert('系统错误，请联系管理员。');
            $log.info('保存邮件模板失败。');
        });  
    };

    //保存邮件自定义模板
    $scope.save_mail_custom = function(){
        $scope.can_custom = '0';
        var post_data = {   
            save_mail_custom:'custom',
            mail_topic:$scope.mail_tpl_topic,
            mail_html:$scope.mail_tpl_html,
            mail_txt:$scope.mail_tpl_txt
        };
        $log.info($scope.can_custom);

        $http.post('/fuck/systems/store_manage.php', post_data).success(function(data) {  
            if(data == 'ok'){
                $scope.plug_alert('success','已保存。','fa fa-smile-o');
                $scope.can_custom = '1';
            }else{
                $scope.plug_alert('danger','系统错误，请联系管理员。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {  
            alert('系统错误，请联系管理员。');
            $log.info('保存自定义模板失败。');
        });  
    };

    //邮件测试
    $scope.test_send_mail = function(method){

        if(method == 'send_express'){
            $scope.save_express_mail(); //测试前先保存邮件

            var post_data = {
                send_mail:'test_mail',
                model_name:method,
                station:$scope.express_mail_modal_station,
                store:$scope.express_mail_modal_store,
                to_mail:$scope.test_mail
            };
        }else if(method == 'tpl'){
            $scope.save_mail_tpl(); //测试前先保存邮件

            var post_data = {
                send_mail:'test_mail',
                model_name:method,
                id:$scope.edit_tpl_id,
                station:$scope.mail_tpl_station,
                store:$scope.mail_tpl_store,
                to_mail:$scope.test_mail
            };
        }else if(method == 'custom'){
            $scope.save_mail_custom(); //测试前先保存邮件

            var post_data = {
                send_mail:'test_mail',
                model_name:method,
                station:$scope.now_station,
                store:$scope.now_store_bar,
                to_mail:$scope.test_mail
            };
        }

        $http.post('/fuck/mail/send_mail.php', post_data).success(function(data) {  
            if(data == 'sended'){
                $scope.plug_alert('success','邮件已发送至：'+$scope.test_mail,'fa fa-send');
            }else{
                $scope.plug_alert('danger','发送失败，请联系管理员。','fa fa-ban');
                $log.info(data);
            }
        }).error(function(data) {  
            alert('系统错误，请联系管理员。');
            $log.info('邮件测试失败。');
        });  
    };

    // 邮件预览
    $scope.demo_mail_custom = function(order_id){
        
        $scope.demo_show = 1;

        var post_data = {
                demo_mail:order_id,
                station:$scope.now_station,
                method:'custom',
                mail_tpl_topic:$scope.mail_tpl_topic,
                mail_tpl_html:$scope.mail_tpl_html,
                mail_tpl_txt:$scope.mail_tpl_txt
            };

        $http.post('/fuck/systems/store_manage.php', post_data).success(function(data) {
            $log.info(data)
            document.getElementById('mail_info_topic').innerHTML = data.mail_topic;
            document.getElementById('mail_info_html').innerHTML = data.mail_html;
        }).error(function(data) {  
            alert("系统错误，请联系管理员。");
            $log.info("error:邮件内容读取失败。");
        }); 

    };

    //读取错误邮件info
    $scope.read_error_mail = function(){
        $http.get('/fuck/mail/amazon_send_mail.php', {
            params:{
                read_error_mail:'read'
            }
        }).success(function(data) {
            $scope.error_mail = data;
            $scope.plug_alert('success','发信完成。','fa fa-smile-o');
        }).error(function(data) {
            alert("系统错误，请联系管理员。");
            $log.info("error:邮件内容读取失败。");
        });
    };

    // 发信
    $scope.amz_mail_custom_items = function(){
        // $log.info($scope.my_checked_items);
        
        $timeout(function(){
            if($scope.can_custom == '1'){
                $scope.shadow('open','ss_make','Amazon正在发信，请稍后。');
                var post_data = {
                    send_mail:'amazon',
                    store:$scope.now_store_bar,
                    station:'Amazon',
                    mail_tpl:'custom',
                    my_checked_items:$scope.my_checked_items};

                    $http.post('/fuck/mail/amazon_send_mail.php', post_data).success(function(data) {
                        if(data == 'ok'){
                            $scope.plug_alert('success','已经提交发信。','fa fa-smile-o');
                        }else{
                            $log.info(data);
                            $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
                        }
                    $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                }).error(function(data) {
                    alert("系统错误，请联系管理员。");
                    $log.info("error:发信失败。");
                });
            }else{
                $scope.plug_alert('danger','保存失败，请重试。','fa fa-ban');
            }

        },5000);   
        
    };

    $scope.rku_mail_custom_items = function(){

        // $log.info($scope.my_checked_items);
        $timeout(function(){
            if($scope.can_custom == '1'){
                $scope.shadow('open','ss_make','Rakuten正在发信，请稍后。');
                var post_data = {
                    send_mail:'rakuten',
                    store:$scope.now_store_bar,
                    station:'Rakuten',
                    mail_tpl:'custom',
                    my_checked_items:$scope.my_checked_items};

                    $http.post('/fuck/mail/rakuten_send_mail.php', post_data).success(function(data) {
                        if(data == 'ok'){
                            $scope.plug_alert('success','已经提交发信。','fa fa-smile-o');
                        }else{
                            $log.info(data);
                            $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
                        }
                    $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                }).error(function(data) {
                    alert("系统错误，请联系管理员。");
                    $log.info("error:发信失败。");
                });
            }else{
                $scope.plug_alert('danger','保存失败，请重试。','fa fa-ban');
            }

        },5000); 

    };

    $scope.pyahoo_mail_custom_items = function(){

            // $log.info($scope.my_checked_items);
            $timeout(function(){
                if($scope.can_custom == '1'){
                    $scope.shadow('open','ss_make','P_Yahoo正在发信，请稍后。');
                    var post_data = {
                        send_mail:'pyahoo',
                        store:$scope.now_store_bar,
                        station:'P_Yahoo',
                        mail_tpl:'custom',
                        my_checked_items:$scope.my_checked_items};

                        $http.post('/fuck/mail/pyahoo_send_mail.php', post_data).success(function(data) {
                            if(data == 'ok'){
                                $scope.plug_alert('success','已经提交发信。','fa fa-smile-o');
                            }else{
                                $log.info(data);
                                $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
                            }
                    $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                }).error(function(data) {
                    alert("系统错误，请联系管理员。");
                    $log.info("error:发信失败。");
                });
            }else{
                $scope.plug_alert('danger','保存失败，请重试。','fa fa-ban');
            }
        },5000);

    };

    $scope.yahoo_mail_custom_items = function(){

            // $log.info($scope.my_checked_items);
            $timeout(function(){
                if($scope.can_custom == '1'){
                    $scope.shadow('open','ss_make','Yahoo正在发信，请稍后。');
                    var post_data = {
                        send_mail:'yahoo',
                        store:$scope.now_store_bar,
                        station:'Yahoo',
                        mail_tpl:'custom',
                        my_checked_items:$scope.my_checked_items};

                        $http.post('/fuck/mail/yahoo_send_mail.php', post_data).success(function(data) {
                            if(data == 'ok'){
                                $scope.plug_alert('success','已经提交发信。','fa fa-smile-o');
                            }else{
                                $log.info(data);
                                $scope.plug_alert('danger','发信失败，请联系管理员。','fa fa-ban');
                            }
                    $timeout(function(){$scope.shadow('close');},500); //关闭shadow
                }).error(function(data) {
                    alert("系统错误，请联系管理员。");
                    $log.info("error:发信失败。");
                });
            }else{
                $scope.plug_alert('danger','保存失败，请重试。','fa fa-ban');
            }
        },5000);

    };

}]);