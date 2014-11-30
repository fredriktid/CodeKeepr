(function ($) {

    $.fn.editor = function(options) {
        var context = this;
        this.mode = '';

        this.init = function() {
            $('.chosen').chosen({
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
            return $('.tags').editorTags();
        }

        this.update = function() {

            switch (this.mode) {
                case 'main': {
                    $('input.search-query').select();
                } break;

                case 'edit': {
                    $('input.title').select();
                    this.tags().init(context);
                } break;
            }

            return this;
        }

        this.searcher = function() {
            $('input.autocomplete').each(function (index, value) {
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
            });
        }

        this.data('context', this);

        return this;
    }
}(jQuery));
