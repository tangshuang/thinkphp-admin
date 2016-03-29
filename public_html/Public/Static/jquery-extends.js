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
}(['jquery'],function() {

    $.serializeObject = function () {
        var a, o, h, i, e;
        a = this.serializeArray();
        o = {};
        h = o.hasOwnProperty;
        for (i = 0; i < a.length; i++) {
            e = a[i];
            if (!h.call(o, e.name)) {
                o[e.name] = e.value;
            }
        }
        return o;
    };

    $.preLoadImage = function(src,callback) {
        var imgObj = new Image();
        if(typeof callback === 'function') {
            imgObj.onload = function() {
                callback(imgObj);
            };
        }
        imgObj.src = src;
    };

});