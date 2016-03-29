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

    /**
     * ajax加载页面，同时还可以替换url
     * @param url
     * @param find
     * @param replace
     * @param callback
     */
    function ajaxPage(url,find,replace,callback) {
        $.ajax({
            url: url,
            beforeSend: function() {
                $('html').addClass('ajax-loading');
            },
            success: function(result) {

                var state = {
                    title: document.title,
                    url: window.location.href,
                    selector: replace,
                    content: $(find).html()
                };
                history.pushState(state, state.title, state.url);

                var $html = $('<div></div>').append(result);
                var title = $html.find('title').text();

                if(!find)
                    find = '#ajax-body';
                if(!replace)
                    replace = find;
                var content = $html.find(find).html();

                $(replace).html(content);

                if(callback && typeof(callback) === "function") {
                    callback(result);
                }

                state = {
                    title: title,
                    url: url,
                    selector: replace,
                    content: content
                };
                window.history.replaceState(state,title,url);
            },
            complete: function() {
                $('html').removeClass('ajax-loading');
            }
        });
    }

    if(history && history.pushState) {
        var loaded = false;
        $(window).bind("popstate", function() {
            var state = history.state;

            if (!loaded) {
                loaded = true;
            }
            else {
                $(state.selector).html(state.content);
                document.title = state.title;
                window.history.replaceState(state,state.title,state.url);
            }
        });
    }

    /**
     * ajax请求url，并把目标页面中的find元素的html替换当前页面的replace元素中的html
     * @param url 目标页面url
     * @param find 目标页面要找的对象选择器
     * @param replace 当前页面要替换html的对象的选择器
     * @param title 是否替换标题，如果设置了值，则使用该值作为新页面的标题
     * @param callback 回调函数
     */
    function ajaxGet(url, find, replace, title, callback) {
        $.ajax({
            url: url,
            beforeSend: function() {
                $('html').addClass('ajax-loading');
            },
            success: function(result) {
                var $html = $('<div></div>').append(result);

                if(title === true)
                    title = $html.find('title').text();
                if(title)
                    $('title').text(title);

                if(!find)
                    find = '#ajax-body';
                if(!replace)
                    replace = find;
                var content = $html.find(find).html();
                if(content) {
                    $(replace).html(content);
                }

                if(callback && typeof(callback) === "function") {
                    callback(result);
                }
            },
            complete: function() {
                $('html').removeClass('ajax-loading');
            }
        });
    }

    /**
     * ajax提交post，并把目标页面中的find元素的html替换当前页面的replace元素中的html
     * @param url 目标页面url
     * @param data 要post的数据
     * @param find 目标页面要找的对象选择器
     * @param replace 当前页面要替换html的对象的选择器
     * @param title 是否替换标题，如果设置了值，则使用该值作为新页面的标题
     * @param callback 回调函数
     */
    function ajaxPost(url, data, find, replace, title, callback) {
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            beforeSend: function() {
                $('html').addClass('ajax-loading');
            },
            success: function(result) {
                var $html = $('<div></div>').append(result);

                if(title === true)
                    title = $html.find('title').text();
                if(title)
                    $('title').text(title);

                if(!find)
                    find = '#ajax-body';
                if(!replace)
                    replace = find;
                var content = $html.find(find).html();
                if(content) {
                    $(replace).html(content);
                }

                if(callback && typeof(callback) === "function") {
                    callback(result);
                }
            },
            complete: function() {
                $('html').removeClass('ajax-loading');
            }
        });
    }

    $.extend({
        Page : {
            ajaxGet: ajaxGet,
            ajaxPost: ajaxPost,
            ajaxPage: ajaxPage
        }
    });
});