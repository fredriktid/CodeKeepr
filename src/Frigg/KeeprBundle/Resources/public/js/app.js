$(document).ready(function() {

    $('input[name=query]')
        .select()
        .autocomplete({
            source: function(request, response) {
                jQuery.ajax({
                    url: '/search/autocomplete/post',
                    dataType: 'json',
                    data: {
                        query: request.term,
                        method: 'json'
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            type:"json",
            select: function(event, ui)
            {
            }
        });
});
