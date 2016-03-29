define(['jquery','tip','dialog','page','uploadify','uploader','tinymce','dragsort','uploadify','uploader','jqueryui','datetimepicker','colorpicker'],function(require,exports){
    require('jquery');

    require('tip');
    require('dialog');
    require('page');
    require('uploadify');
    require('uploader');
    require('tinymce');

    require('dragsort');
    require('uploadify');
    require('uploader');

    require('jqueryui');
    require('datetimepicker');

    var $colorpicker = require('colorpicker');

    // 表单中的编辑器
    function editor_init() {
        // 构建编辑器
        var $editors = $('.editor');
        if($editors.length > 0) {
            // 禁止静态提交表单，如果静态提交，会导致无法第二次打开编辑器
            $(document).on('submit','form.form',function(e){
                e.stopImmediatePropagation();
                $.Form.ajaxSubmit(this,function(result){
                    $.Tip.popup(result.info);
                    if(result.status == 1) {
                        var url = result.url || window.location.href;
                        $.Page.ajaxGet(url,'#content','#content',false,function(){
                            window.history.replaceState(null,null,url);
                        });
                        $.Dialog.destroy(); // 如果表单是在弹出层中打开，那么直接销毁所有的弹出层
                    }
                });
                return false;
            });
            // 初始化编辑器
            var defaults = {
                language : "zh_CN",
                menubar: false,
                convert_urls: false,
                plugins: [
                    'link imagemanager',
                    'hr',
                    'visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                    'save table contextmenu directionality template paste textcolor'
                ],
                toolbar: [
                    'undo redo blockquote hr bullist numlist imagemanager media link unlink removeformat paste code fullscreen',
                    'bold italic underline strikethrough forecolor fontsizeselect indent outdent alignleft aligncenter alignright alignjustify'
                ], // https://www.tinymce.com/docs/advanced/editor-control-identifiers/#toolbarcontrols
                fontsize_formats: '10px 11px 12px 14px 16px 18px 24px 36px',
                contextmenu: "cut copy paste cancel link code",
                contextmenu_never_use_native: true
            };
            $editors.each(function(index){
                var $this = $(this),
                    selector = '#' + $this.attr('id'),
                    height = $this.attr('data-height'),
                    language_url = $this.attr('data-language_url'),
                    imagemanager_url = $this.attr('data-imagemanager_url');
                var options = $.extend(defaults,{
                    selector : selector,
                    height : height,
                    language_url : language_url,
                    imagemanager_url : imagemanager_url
                });
                tinymce.execCommand('mceRemoveEditor',true,$this.attr('id'));
                $(selector).tinymce(options);
            });
        }
    }

    // 表单中的图片上传组件
    function uploader_init() {
        var $uploaders = $('.upload-picture');
        if($uploaders.length > 0) {
            $uploaders.each(function(index){
                var $this = $(this), selector = '#' + $this.attr('id'), uploader = $this.attr('data-uploader'), type = $this.attr('data-type');
                if(type == 'one') {
                    var field = $this.attr('data-field'),
                        title = $this.attr('data-title'),
                        image = $this.attr('data-image');
                    $.HHuploadify.initOne(selector,uploader,field,title);
                    if(image && image != 'null') {
                        image = JSON.parse(image);
                        $.HHuploadify.resetOne(selector,image,field,title);
                    }
                }
                else if(type == 'count') {
                    var fields = $this.attr('data-fields'), titles = $this.attr('data-titles'), images = $this.attr('data-images');
                    fields = JSON.parse(fields);
                    titles = titles ? JSON.parse(titles) : null;
                    $.HHuploadify.initCount(selector,uploader,fields,titles);
                    if(images && images != 'null') {
                        images = JSON.parse(images);
                        $.HHuploadify.resetCount(selector,images,fields,titles);
                    }
                }
                else if(type == 'multi') {
                    var field = $this.attr('data-field'),
                        title = $this.attr('data-title'),
                        images = $this.attr('data-images');
                    $.HHuploadify.init(selector,uploader,field,title);
                    if(images && images != 'null') {
                        images = JSON.parse(images);
                        $.HHuploadify.reset(selector,images,field,title);
                    }
                }
            });
        }
    }

    // 表单中的时间选择器
    function datetimepicker_init(){
        // datetime
        $('input[type=datetime]').datetimepicker({
            showSecond: true,
            timeFormat: 'HH:mm:ss'
        });
        // date
        $('input[type=date]').attr('type','text').attr('readonly','readonly').datepicker();
        // time
        $('input[type=time]').timepicker();
    }

    // 取色器
    function colorpicker_init() {
        $colorpicker.register();
    }

    editor_init();
    uploader_init();
    datetimepicker_init();
    colorpicker_init();

    exports.reload = function(){
        editor_init();
        uploader_init();
        datetimepicker_init();
        colorpicker_init();
    };
});