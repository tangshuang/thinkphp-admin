/**
 * Dialog jQuery Plugin
 * jQuery弹出窗口插件
 */

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

    // 向body添加必要的div
    var html = '<div class="dialog">' +
            '<div class="dialog-bg"></div>' +
            '<div class="dialog-main">' +
                '<a href="javascript:" class="dialog-close">&times;</a>' +
                '<div class="dialog-container">' +
                    '<div class="dialog-title"></div>' +
                    '<div class="dialog-content"></div>' +
                '</div>' +
            '</div>' +
        '</div>';

    // 创建一个新的dialog
    function create(id,title,content) {
        id = id || 'dialog';

        var $this = $('#' + id);
        if($this.length == 0) {
            var $html = $(html);
            $html.attr('id',id);
            $html.appendTo('body');
            $this = $('#' + id);
        }

        title ? $this.find('.dialog-title').html(title) : $this.find('.dialog-title').remove();
        content ? $this.find('.dialog-content').html(content) : $this.find('.dialog-content').remove();
    }

    // 打开一个dialog
    function show(id,title,content,callback) {
        id = id || 'dialog';
        var $this = $('#' + id);
        title ? $this.find('.dialog-title').html(title) : $this.find('.dialog-title').remove();
        content ? $this.find('.dialog-content').html(content) : $this.find('.dialog-content').remove();
        $this.fadeIn('fast',function(){
            if(typeof callback == 'function') callback();
        });
    }

    // 创建被打开一个dialog，它是create和show的合体
    function open(id,title,content,callback) {
        id = id || 'dialog';

        var $this = $('#' + id);
        if($this.length == 0) {
            var $html = $(html);
            $html.attr('id',id);
            $html.appendTo('body');
            $this = $('#' + id);
        }

        title ? $this.find('.dialog-title').html(title) : $this.find('.dialog-title').remove();
        content ? $this.find('.dialog-content').html(content) : $this.find('.dialog-content').remove();

        $this.fadeIn('fast',function(){
            if(typeof callback == 'function') callback();
        });
    }

    // 关闭dialog
    function close(id,callback) {
        id = id || 'dialog';
        $('#' + id).fadeOut('fast',function(){
            if(typeof callback == 'function') callback();
        });
    }

    // 移除dialog
    function remove(id,callback) {
        id = id || 'dialog';
        $('#' + id).remove();
        if(typeof callback == 'function') callback();
    }

    // 销毁所有dialog
    function destroy(callback) {
        $('.dialog').fadeOut('fast',function(){
            $('.dialog').remove();
            if(typeof callback == 'function') callback();
        });
    }

    // 全局变量方法
    $.extend({
        Dialog : {
            open : open,
            create : create,
            show : show,
            close : close,
            remove : remove,
            destroy : destroy
        }
    });

    // 对象方法
    $.fn.extend({
        dialog: function(title,contentSelector,callback) {
            $(document).off('click',this.selector).on('click',this.selector,function(e){
                e.preventDefault();
                var $this = $(this), id = $this.attr('data-id') || 'dialog', url = $this.attr('href') || $this.attr('data-url');
                $.get(url,function(result){
                    var content;
                    if(contentSelector === false){ // 如果contentSelector为false，那么直接打开目标页的内容
                        content = result;
                    }
                    else {
                        var $html = $('<div>' + result + '</div>');
                        content = $html.find(contentSelector).html();
                    }
                    open(id,title,content);
                    if(typeof callback == 'function') callback(result);
                });
                return false;
            });
        }
    });

    // 关闭dialog
    $(document).on('click','.dialog .dialog-close',function(e){
        e.preventDefault();
        var $dialog = $(this).parent().parent();
        $dialog.fadeOut('fast',function(){
            $dialog.remove();
        });
    });

});