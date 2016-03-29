!function(dependencies,factory){
    // amd || cmd
    if(typeof define == 'function' && (define.cmd || define.amd)) {
        define(dependencies,function() {
            return factory();
        });
    }
    else {
        var ex = factory();
        // CommonJS NodeJS
        if(typeof module !== 'undefined' && typeof exports === 'object') {
            module.exports = ex;
        }
    }
}(['jquery'],function(){
    // 函数
    var ajaxSubmit = function(selector,callback) {
        var $form = $(selector);
        if ($form.attr('ajax-disabled') != undefined)
            return true;

        var options = {
            url: $form.attr('action') || window.location.href,
            type: $form.attr('method') || 'POST',
            beforeSend: function() {
                $('html').addClass('ajax-loading');
            },
            success: function(result) {
                if(callback && typeof(callback) === "function") {
                    callback(result);
                }
            },
            complete: function() {
                $('html').removeClass('ajax-loading');
            }
        };

        if (!!$form.attr('enctype') && $form.attr('enctype').toLowerCase() === 'multipart/form-data') {
            var formData = new FormData();
            var $files = $form.find('input[type="file"][name]');
            $files.each(function() {
                if ('files' in this && this.files.length > 0) {
                    // ToDo: Support Multiple on any input?
                    // Just need a loop here..
                    formData.append(this.name, this.files[0]);
                }
            });

            var $noFiles = $form.find(':not(input[type="file"])');
            $.each($noFiles.serializeArray(), function(i, pair) {
                formData.append(pair.name, pair.value);
            });

            options.data = formData;
            options.method = 'POST';
            options.contentType = false;
            options.processData = false;
        } else {
            options.data = $form.serializeArray();
        }

        $.ajax(options);
    };

    // 全局对象方法
    $.extend({
        Form : {
            ajaxSubmit : ajaxSubmit
        }
    });

    // 方法
    $.fn.extend({
        ajaxSubmit : function(callback) {
            $(document).off('submit',this.selector).on('submit',this.selector,function (e) {
                e.preventDefault();
                var $form = $(this);
                if ($form.attr('ajax-disabled') != undefined)
                    return true;

                ajaxSubmit(this,callback);
                return false;
            });
        }
    });


});