define(['jquery','tip','page','form','dialog','./fields'],function(require){
    require('jquery');
    require('tip');
    require('page');
    require('form');
    require('dialog');
    var $fields = require('./fields');

    // 清除缓存数据
    $('#header .user-bar .trash-cache').on('click',function(e){
        e.preventDefault();
        var $this = $(this),url = $this.attr('href');
        $.get(url,function(result){
            $.Tip.show($this,result.info);
        }).error(function(){
            $.Tip.show($this,'网络错误，刷新重试。');
        });
    });

    // 退出登录
    $('#header .user-bar .logout').on('click',function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        $.get(url,function(result){
            $.Tip.popup(result.info);
            if(result.status == 1) {
                window.location.href = result.url;
            }
        });
    });

    // 列表中，通过ajax请求，注意，这里仅限于内容主区域，如果有其他需求，需要另外再撰写
    $(document).on('click','#main .ajax-get',function(e){
        e.preventDefault();
        var $this = $(this),
            url = $this.attr('href') || $this.attr('data-url'),
            replace = $this.attr('data-replace') || '#content';
        $.get(url,function(result){
            $.Tip.popup(result.info);
            if(result.status == 1) {
                var url = window.location.href;
                $.Page.ajaxGet(url,'#content');
            }
        });
    });

    // 快速编辑
    $(document).on('click','.quick-edit-text',function(e){
        var $this = $(this);
        if($this.hasClass('editing'))
            return;
        var input = '<input type="number" maxlength="4" style="width:2.5em;">';
        $this.addClass('editing').html(input);
        //$this.find('input').focus();
    });
    $(document).on('focusout','.quick-edit-text input',function(e){
        var $this = $(this).parent(),$input = $(this),value = $input.val(), url = $this.attr('data-url'), defaultValue = $this.attr('data-value'), field = $this.attr('data-name');
        if(value == '') {
            $this.html(defaultValue);
            $this.removeClass('editing');
            $.Tip.popup('未填写排序值');
            return;
        }
        if(value != defaultValue) {
            $.post(url,field + '=' + value,function(result){
                $.Tip.popup(result.info);
                if(result.status == 1) {
                    var url = window.location.href;
                    $.Page.ajaxGet(url,'#content');
                }
            });
        }
        $this.html(value);
        $this.removeClass('editing');
    });

    // 表单静态提交，并且刷新本页
    $('form').ajaxSubmit(function(result){
        $.Tip.popup(result.info);
        if(result.status == 1) {
            var url = result.url || window.location.href;
            $.Page.ajaxGet(url,'#content','#content',false,function(){
                window.history.replaceState(null,null,url);
            });
            $.Dialog.destroy(); // 如果表单是在弹出层中打开，那么直接销毁所有的弹出层
        }
    });

    // 通过dialog打开链接
    $('.btn-dialog').dialog(false,'#content',function(result){
        if(typeof result.status !== 'undefined' && result.status == 0) {
            $.Dialog.destroy();
            $.Tip.popup(result.info);
            throw new Error(result.info);
        }
        $fields.reload();
    });
    $('.open-in-dialog').dialog(false,false);

});