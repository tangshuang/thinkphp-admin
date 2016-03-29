define(['jquery','tip'],function(require){
    require('tip');

    $('#main form').on('submit', function() {
        var $this = $(this).find('button');
        var $form = $('#main form'), action = $form.attr('action'), data = $form.serialize();
        $.post(action,data,function(result){
            $.Tip.show($this,result.info);
            if(result.status == 1) {
                window.location.href = result.url;
                return false;
            }
        }).error(function(){
            $.Tip.popup('网络错误，请重试。');
        });
        return false;
    });
});