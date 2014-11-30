(function ($) {

    $.fn.codekeepr = function() {

        var context = this;
        this.mode = '';

        this.init = function() {

            this.find('.chosen').chosen({
                disable_search_threshold: 5
            });

            this.find('input.autocomplete').each(function (index, value) {
                $(this).codekeeprSearch().autocomplete();
            });

            return this;
        }

        this.setMode = function(mode) {

            mode = mode || this.mode;

            this.mode = mode;

            return this;
        }

        this.update = function() {

            switch (this.mode) {

                case 'main':
                    this.find('input.search-query').select();
                    break;

                case 'edit':
                    this.find('input.title').select();
                    this.find('.tags').codekeeprTags().buildForm();
                    break;

                default:
                    // ...
            }

            return this;
        }

        this.data('context', this);

        return this;
    }

}(jQuery));
