var app=angular.module('myApp');

app.directive('onFinishRenderFilters', ['$timeout',function($timeout) {
	return {
        restrict: 'A',
        link: function(scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function() {
                    scope.$emit('ngRepeatFinished');
                });
            }
        }
    };
}]);

app.directive('contenteditable', function() {
    return {
        restrict: 'A' ,
        require: '?ngModel',
        link: function(scope, element, attrs, ngModel) {
            // 初始化 编辑器内容
            if (!ngModel) {
                return;
            } // do nothing if no ng-model
            // Specify how UI should be updated
            ngModel.$render = function() {
                element.html(ngModel.$viewValue || '');
            };
            // Listen for change events to enable binding
            element.on('blur keyup change', function() {
                scope.$apply(readViewText);
            });
            // No need to initialize, AngularJS will initialize the text based on ng-model attribute
            // Write data to the model
            function readViewText() {
                var html = element.html();
                // When we clear the content editable the browser leaves a <br> behind
                // If strip-br attribute is provided then we strip this out
                if (attrs.stripBr && html === '<br>') {
                    html = '';
                }
                ngModel.$setViewValue(html);
            }

            // 创建编辑器
            var editor = new wangEditor(element);
            editor.config.menus = [
                'source',
                '|',
                'fontsize',
                'head',
                '|',
                'forecolor',
                'bgcolor',
                'quote',
                'bold',
                'underline',
                'italic',
                'strikethrough',
                'eraser',
                '|',
                'alignleft',
                'aligncenter',
                'alignright', 
                'table', 
                '|',
                'link',
                'unlink',
                'fullscreen'
            ];
            editor.create();
        }
    };
});