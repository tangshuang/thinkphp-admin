define(['jquery'],function(){
    $(document).on('click','.confirm',function(e){
        var $this = $(this), msg = $this.attr('data-confirm') || '你确定执行该操作吗？';
        var cf = confirm(msg);
        if(!cf) e.stopImmediatePropagation(); // 阻止事件追加
        return cf ? true : false;
    });
});