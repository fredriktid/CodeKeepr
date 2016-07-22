(function ($) {

    $.fn.codekeeprSearch = function() {

        var context = this;

        var delay = (function() {
            var timer = 0;
            return function(callback, ms){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        })();

        this.autocomplete = function() {
            var searchType = $(this).data('type');
            $(this).autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        url: '/search/autocomplete/' + searchType,
                        dataType: 'json',
                        data: {
                            query: request.term,
                            method: 'json'
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                type: 'json',
                select: function (event, ui) {
                    if (searchType == 'post') {
                        window.location.replace(ui.item.url);
                    }
                }
            });
        };

        this.list = function(callback) {
            var spinnerText = '';

            this.bind('keyup', function() {
                var query = $(this).val();
                if (query.length < 1) {
                    return false;
                }

                $('#posts').hide();

                delay(function() {
                    $.ajax({
                        url: '/search/list',
                        data: {
                            'query': query
                        },
                        success: function(data) {
                            $('#posts').html(data).show();
                            if (typeof callback === 'function') {
                                callback();
                            }
                        },
                        error: function(data) {
                        }
                    });
                }, 200);
            });
        };

        this.data('context', this);

        return this;
    }
}(jQuery));
