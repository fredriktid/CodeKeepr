(function ($) {

    $.fn.codekeeprSearch = function() {

        var context = this;

        this.autocomplete = function(callback) {
            var spinnerText = '',
                delay = (function(){
                var timer = 0;
                return function(callback, ms){
                    clearTimeout (timer);
                    timer = setTimeout(callback, ms);
                };
            })();

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
                            $('#posts').html(data).slideDown('fast');
                            callback();
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
