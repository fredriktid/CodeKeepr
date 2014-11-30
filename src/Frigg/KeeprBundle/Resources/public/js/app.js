(function ($) {

    $.fn.codekeepr = function(options) {
        var context = this;
        this.mode = '';

        this.init = function() {
            this.find('.chosen').chosen({
                disable_search_threshold: 5
            });

            this.searcher();

            return this;
        }

        this.setMode = function(mode) {
            mode = mode || this.mode;

            this.mode = mode;

            return this;
        }

        this.tags = function() {
            return this.find('.tags').codekeeprTags();
        }

        this.update = function() {
            switch (this.mode) {
                case 'main':
                    this.find('input.search-query').select();
                    break;
                case 'edit':
                    this.find('input.title').select();
                    this.tags().init(context);
                    break;
                default:
                    // ...
            }

            return this;
        }

        this.searcher = function() {
            this.find('input.autocomplete').each(function (index, value) {
                var searchType = $(this).data('type');
                var searchTarget = $(this).data('target');
                $(this).autocomplete({
                    source: function (request, response) {
                        jQuery.ajax({
                            url: searchTarget,
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
            });
        }

        this.data('context', this);

        return this;
    }
}(jQuery));
