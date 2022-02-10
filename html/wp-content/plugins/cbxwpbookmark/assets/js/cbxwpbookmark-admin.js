(function ($) {
    'use strict';
    $(document).ready(function () {
        //page scroll
        $(window).on('scroll', function () {
            if ($(window).scrollTop() >= 30) {
                $('div.cbxwpbookmark_container_header').addClass('fixed-header');
            } else {
                $('div.cbxwpbookmark_container_header').removeClass('fixed-header');
            }
        });//end scroll
    });//end dom ready
})(jQuery);
