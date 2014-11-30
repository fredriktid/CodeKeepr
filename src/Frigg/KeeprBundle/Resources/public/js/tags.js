(function ($) {

    $.fn.codekeeprTags = function(options) {

        var context = this;

        this.app = false;
        this.tags = false;

        this.init = function (app) {

            this.app = app;
            this.tags = $(this);

            var $add = $('<a href="#" class="add_tag_link margin-bottom btn"><i class="icon-tag"></i> Add tag</a>');
            var $new = $('<li></li>').append($add);

            $(this).find('li').each(function () {
                context.addDelete($(this));
            });

            $(this).prepend($new);

            $(this).data('index', $(this).find(':input').length);

            $add.on('click', function (e) {
                e.preventDefault();
                context.addForm(context.tags, $new);
            });
        }

        this.addForm = function($button, $new) {

            var prototype = $button.data('prototype');
            var index = $button.data('index');

            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__name__/g, index);

            $button.data('index', index + 1);

            var $form = $('<li></li>').append(newForm);

            $new.after($form);

            // init autocomplete
            context.app.searcher();

            this.addDelete($form);
        }

        this.addDelete = function($form) {
            var $deleteForm = $('<a href="#" class="btn btn-danger btn-tag-delete"><i class="icon-trash icon-white"></i></a>');
            $form.prepend($deleteForm);

            $deleteForm.on('click', function(e) {
                e.preventDefault();
                $form.remove();
            });
        }

        this.data('context', this);

        return this;
    }
}(jQuery));
