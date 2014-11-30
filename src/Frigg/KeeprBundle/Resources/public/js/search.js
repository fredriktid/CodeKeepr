(function ($) {

    $.fn.codekeeprSearch = function() {

        var context = this;

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

        }

        this.data('context', this);

        return this;
    }
}(jQuery));
