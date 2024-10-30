jQuery(document).ready(function($) {

    var checkbox = $('#hami-list p input[type="checkbox"]');
    var value = parseInt($('.hami-count').text());
    var status = $('.hami-status');

    $(checkbox).each(function() {
        if ( this.checked ) {
            value--;
            $(this).next().find(status).addClass('dashicons-hidden');
        } else {
            $(this).next().find(status).addClass('dashicons-visibility');
        }
    });

    $('.hami-count').text(value);

    $(checkbox).change(function() {
        if ( this.checked ) {
            value--;
            $(this).next().find(status).removeClass('dashicons-visibility');
            $(this).next().find(status).addClass('dashicons-hidden');
        } else {
            value++;
            $(this).next().find(status).removeClass('dashicons-hidden');
            $(this).next().find(status).addClass('dashicons-visibility');
        }
        $('.hami-count').text(value);
    });

});