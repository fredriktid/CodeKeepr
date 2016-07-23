(function ($) {

    $.fn.codekeeprPostNew = function() {

        var context = this;
        this.mode = '';

        this.init = function() {
            this.find('input#frigg_keeprbundle_post_topic').focus();

            return this;
        };

        this.setMode = function(mode) {
            mode = mode || this.mode;
            this.mode = mode;

            return this;
        };

        this.data('context', this);

        return this;
    };

}(jQuery));
