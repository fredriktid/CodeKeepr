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


        this.update = function() {

            switch (this.mode) {
                case 'main': {
                    $('input[name=query]').select();
                } break;

                case 'edit':
                {
                    var $tagLinkAdd = $('<a href="#" class="add_tag_link margin-bottom btn"><i class="icon-tag"></i> Add tag</a>');
                    var $tagLinkNew = $('<li></li>').append($tagLinkAdd);

                    // Get the ul that holds the collection of tags
                    $tagGroup = $('ul.tags');

                    // add a delete link to all of the existing tag form li elements
                    $tagGroup.find('li').each(function () {
                        context.addTagFormDeleteLink($(this));
                    });

                    // add the "add a tag" anchor and li to the tags ul
                    $tagGroup.prepend($tagLinkNew);

                    // count the current form inputs we have (e.g. 2), use that as the new
                    // index when inserting a new item (e.g. 2)
                    $tagGroup.data('index', $tagGroup.find(':input').length);

                    $tagLinkAdd.on('click', function (e) {
                        // prevent the link from creating a "#" on the URL
                        e.preventDefault();

                        // add a new tag form (see next code block)
                        context.addTagForm($tagGroup, $tagLinkNew);
                    });
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

        this.addTagForm = function($tagGroup, $tagLinkNew) {
            // Get the data-prototype explained earlier
            var prototype = $tagGroup.data('prototype');

            // get the new index
            var index = $tagGroup.data('index');

            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__name__/g, index);

            // increase the index with one for the next item
            $tagGroup.data('index', index + 1);

            // Display the form in the page in an li, before the "Add a tag" link li
            var $newFormLi = $('<li></li>').append(newForm);
            $tagLinkNew.after($newFormLi);

            this.searcher();

            this.addTagFormDeleteLink($newFormLi);

        }

        this.addTagFormDeleteLink = function($tagFormLi) {
            var $removeFormA = $('<a href="#" class="btn btn-danger btn-tag-delete"><i class="icon-trash icon-white"></i></a>');
            $tagFormLi.prepend($removeFormA);

            $removeFormA.on('click', function(e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();

                // remove the li for the tag form
                $tagFormLi.remove();
            });
        }

        this.data('context', this);

        return this;
    }
}(jQuery));
