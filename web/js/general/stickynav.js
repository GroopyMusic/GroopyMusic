
var $mainNav = $('#mainNav');
var $main = $('main');
var stickyoffset = $main.offset().top - $mainNav.outerHeight();

$(window).scroll(function() {
    if ($(this).scrollTop() >= stickyoffset) {
        if(!$mainNav.hasClass('stickytop')) {
            $mainNav.addClass('stickytop');

            $('.nav-item').each(function() {
                $(this).addClass('stickytop')
            });

            $('#menuLogo').fadeIn();
            $('#logo').addClass('hiddenLogo');
            stickyoffset = $main.offset().top - $mainNav.outerHeight();
        }
    }
    else {
        $('#menuLogo').fadeOut();

        if($mainNav.hasClass('stickytop')) {
            $mainNav.removeClass('stickytop');

            $('#logo').removeClass('hiddenLogo');

            $('.nav-item').each(function () {
                $(this).removeClass('stickytop')
            });
        }
    }
});