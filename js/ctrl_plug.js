var app = angular.module('myApp');
app.controller('plugCtrl', ['$rootScope','$scope','$uibModal','$log', function($rootScope,$scope,$uibModal,$log){
    $scope.ok = "ok";
    //active
    $scope.is_active = function(active_class,index){
        var dom = document.querySelectorAll('.'+active_class);
        angular.element(dom).removeClass('active');
        var dom2 = document.querySelector('#'+active_class+index);
        angular.element(dom2).addClass('active');
    }

    //遮罩
    $scope.loading_shadow = function(is_open){
        if(is_open == 'open'){
            $scope.loading_is_open = true;  
        }
        if(is_open == 'close'){
            $scope.loading_is_open = false; 
        }
    }
    $scope.loading_is_open = false;
    $scope.shadow = function(is_open,icon,txt){
        if(is_open == 'open'){
            $scope.ss_is_open = true;  
        }
        if(is_open == 'close'){
            $scope.ss_is_open = false; 
        }

        if(icon == 'ss_syn'){
            $scope.ss_syn = true;   
            $scope.ss_write = false;
            $scope.ss_read = false;
            $scope.ss_make = false; 
        }
        if(icon == 'ss_write'){
            $scope.ss_write = true; 
            $scope.ss_syn = false;  
            $scope.ss_read = false; 
            $scope.ss_make = false; 
        }
        if(icon == 'ss_read'){
            $scope.ss_read = true;
            $scope.ss_syn = false; 
            $scope.ss_write = false;
            $scope.ss_make = false;   
        }
        if(icon == 'ss_make'){
            $scope.ss_make = true;   
            $scope.ss_syn = false; 
            $scope.ss_write = false;
            $scope.ss_read = false;
        }

        $scope.shadow_txt = txt;
    };

    //手风琴
    $scope.oneAtATime = true;

    // $scope.groups = [
    // {
    //   title: 'Dynamic Group Header - 1',
    //   content: 'Dynamic Group Body - 1'
    // },
    // {
    //   title: 'Dynamic Group Header - 2',
    //   content: 'Dynamic Group Body - 2'
    // }
    // ];

    $scope.items = ['Item 1', 'Item 2', 'Item 3'];

    $scope.addItem = function() {
    var newItemNo = $scope.items.length + 1;
    $scope.items.push('Item ' + newItemNo);
    };

    $scope.status = {
    isCustomHeaderOpen: false,
    isFirstOpen: true,
    isFirstDisabled: false
    };
    //alerts
    $scope.plug_alert = function(e,f,i){    
        $scope.alerts = [
            { type: e, msg: f }
        ];
        $scope.alerts.icon = i
    }

    $scope.closeAlert = function(index) {
        //获取class，然后删掉！！！！
        // var dom = document.querySelector('.alert');
        // angular.element(dom).remove();
        $scope.alerts.splice(index, 1); 
    };

    //button
    $scope.singleModel = 1;
    $scope.radioModel = 'Middle';
    $scope.checkModel = {
        left: false,
        middle: true,
        right: false
    };

    $scope.checkResults = [];

    $scope.$watchCollection('checkModel', function () {
        $scope.checkResults = [];
        angular.forEach($scope.checkModel, function (value, key) {
          if (value) {
            $scope.checkResults.push(key);
          }
        });
    });

    //折叠
    $scope.isCollapsed = false;
    $scope.isCollapsedHorizontal = false;

    //日期时间
    $scope.today = function() {
        $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function() {
        $scope.dt = null;
    };

    $scope.inlineOptions = {
        customClass: getDayClass,
        minDate: new Date(),
        showWeeks: true
    };

    $scope.aaa = function(){    //测试弹出值
        alert($scope.ssa)
    }
    
    $scope.dateOptions = {
        //dateDisabled: disabled,   是否选择周六周日
        formatYear: 'yy',
        maxDate: new Date(2020, 5, 22),
        minDate: new Date(),
        startingDay: 1
    };

  // 是否选择周六周日
    // function disabled(data) {
    //     var date = data.date,
    //       mode = data.mode;
    //     return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
    // }plug·

    $scope.toggleMin = function() {
        $scope.inlineOptions.minDate = $scope.inlineOptions.minDate ? null : new Date();
        $scope.dateOptions.minDate = $scope.inlineOptions.minDate;
    };

    $scope.toggleMin();


    $scope.open2 = function() {
        $scope.popup2.opened = true;
    };

    $scope.setDate = function(year, month, day) {
        $scope.dt = new Date(year, month, day);
    };

    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
    $scope.format = $scope.formats[0];
    $scope.altInputFormats = ['M!/d!/yyyy'];

    $scope.popup2 = {
        opened: false
    };

    var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        var afterTomorrow = new Date();
        afterTomorrow.setDate(tomorrow.getDate() + 1);
        $scope.events = [
        {
            date: tomorrow,
            status: 'full'
        },
        {
            date: afterTomorrow,
            status: 'partially'
        }
    ];

    function getDayClass(data) {
        var date = data.date,
        mode = data.mode;
        if (mode === 'day') {
            var dayToCheck = new Date(date).setHours(0,0,0,0);
            for (var i = 0; i < $scope.events.length; i++) {
                var currentDay = new Date($scope.events[i].date).setHours(0,0,0,0);
                if (dayToCheck === currentDay) {
                    return $scope.events[i].status;
                }
            }
        }
    return '';
    }

    //下拉
    $scope.plug_dropdown=function(){
        $scope.click_key="请选择...";

        $scope.isdisabled=true; //默认操作按钮不可见
        $scope.isenabled=false; //默认操作按钮不可见
        // $scope.status = {    //这里会影响到手风琴
        //     isopen: false
        // };

        $scope.toggleDropdown = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.status.isopen = !$scope.status.isopen;
        };

        $scope.click_me=function(e,f){
            $scope.click_key=e;
            $scope.click_id=f;
            $scope.isdisabled=false;    //操作按钮可见
            $scope.isenabled=true;
        }
    }

    //模态框
    $scope.items = ['item1', 'item2', 'item3'];
    $scope.animationsEnabled = true;

    $scope.open = function (size,mytpl,ctrler) {

        mytpl = mytpl+'.html'
        var modalInstance = $uibModal.open({
          animation: $scope.animationsEnabled,
          templateUrl: mytpl,
          controller: ctrler,
          size: size
    });

    modalInstance.result.then(function (selectedItem) {
          $scope.selected = selectedItem;
        }, function () {
        $log.info('Modal dismissed at: ' + new Date());
        });
    };

    $scope.toggleAnimation = function () {
        $scope.animationsEnabled = !$scope.animationsEnabled;
    };
}])

app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance) {

    // $scope.items = items;
    //     $scope.selected = {
    //     item: $scope.items[0]
    // };

    $scope.ok = function () {
        $uibModalInstance.close($scope.selected.item);
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});