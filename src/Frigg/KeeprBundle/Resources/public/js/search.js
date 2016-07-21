(function ($) {

    $.fn.codekeeprSearch = function() {

        var context = this;

        this.autocomplete = function() {
            var spinnerText = '...',
                delay = (function(){
                var timer = 0;
                return function(callback, ms){
                    clearTimeout (timer);
                    timer = setTimeout(callback, ms);
                };
            })();

            this.bind('keyup', function() {
                var query = $(this).val();
                if (query.length < 2) {
                    return false;
                }

                $('#posts').html(spinnerText);

                delay(function() {
                    $.ajax({
                        url: '/search/list',
                        data: {
                            'query': query
                        },
                        success: function(data) {
                            $('#posts').html(data);
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
