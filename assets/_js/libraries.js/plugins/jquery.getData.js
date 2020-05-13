; (function ($) {
    var namespaces = {};
    var method = "get"
    jQuery.extend({
        postData: function (url, data, callback, namespace) {
            method = "post";
            $.getData(url, data, callback, namespace, "post")
        },
        getData: function (url, data, callback, namespace, method = "get") {
            if (namespaces[namespace]) {
                for (var i = 0; i < namespaces[namespace].length; i++) namespaces[namespace][i].abort();
            } else {
                namespaces[namespace] = [];
            }

            var type = "json";



            if (jQuery.isFunction(data)) {
                type = type || callback;
                callback = data;
                data = undefined;
            }



            return namespaces[namespace].push(jQuery.ajax({
                url: url,
                type: method,
                dataType: type,
                data: data,
                success: function (response, status, request) {
                    var d = request.responseJSON;
                    callback(d);
                }
            }));
            //return jQuery.get(url, data, callback, "json");
        }

    });

})(jQuery);

