$(document).ready(function() {

    $('input[name=query]').select();

    $('input.autocomplete').each(function(index, value) {
        var searchType =  $(this).data('type');
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
                window.location.replace(ui.item.url);
            }

        });
    });

});
