
var $mainNav = $('#mainNav');
var stickyoffset = $mainNav.offset().top;

$(window).scroll(function() {
    if ($(this).scrollTop() >= stickyoffset) {
        if(!$mainNav.hasClass('stickytop')) {
            $mainNav.addClass('stickytop');

            $('#logo').addClass('stickytop');

            $('.nav-item.left-nav-item').animate({
                right: "+=100"
            }, 2000, function() {

            });

            $('.nav-item.right-nav-item').animate({
                left: "+=100"
            }, 2000, function() {

            });
        }
    }
    else if($mainNav.hasClass('stickytop')) {
        $mainNav.removeClass('stickytop');
        $('#logo').animate({
            top: "-=150"
        }, 2000, function() {
        });

        $('.nav-item.left-nav-item').animate({
            right: "-=100"
        }, 2000, function() {

        });

        $('.nav-item.right-nav-item').animate({
            left: "-=100"
        }, 2000, function() {

        });
    }
});