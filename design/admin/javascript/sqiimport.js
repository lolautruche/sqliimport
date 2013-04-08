$(document).ready( function() {
    $(".detail_link").click(function( e ) {
        $(this).siblings('.notice').each(function( index ) {
            if( $(this).is(':visible') ) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        e.preventDefault();
    });
});
