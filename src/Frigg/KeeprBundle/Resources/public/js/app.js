$(document).ready(function() {

    $('input[name=query]').select();

    $('a.not').on('click', function(e) {
        e.preventDefault();
        alert('Coming soon...');
    });
});