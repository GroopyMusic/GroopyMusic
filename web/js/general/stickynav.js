var $header = $('header');
var $mainNav = $('#mainNav');
var $main = $('main');
var appearances = 0;
var stickyoffset = $main.offset().top - $mainNav.outerHeight();
$header.css('min-height', $header.outerHeight());


function mainPadding() {
    if($header.outerHeight() == 0) {
        $main.css('margin-top', $mainNav.outerHeight());
    }
}

function mainNavHeight() {
    $mainNav.css('min-height', 'auto');
    $mainNav.css('min-height', $mainNav.outerHeight());
}

$(function() {
    mainPadding();
    mainNavHeight();
    $(window).resize(function () {
        $header.css('min-height', 'auto');
        $header.css('min-height', $header.outerHeight());
        mainPadding();
        mainNavHeight();
    });


    $(window).scroll(function () {
        if ($(this).scrollTop() > stickyoffset) {
            if (!$mainNav.hasClass('stickytop')) {
                $mainNav.addClass('stickytop');

                $('header .nav-item').each(function () {
                    $(this).addClass('stickytop')
                });


                $('#logo').addClass('hiddenLogo');
                stickyoffset = $main.offset().top - $mainNav.outerHeight();

                if (!$('#toc-nav').hasClass('fixed-toc'))
                    $('#toc-nav').addClass('fixed-toc');

                // Bug fix for logo appearing once too quickly
                if(appearances > 0 || $(this).scrollTop() > stickyoffset + 10)
                    $('#menuLogo').fadeIn();

                appearances++;
            }
        }
        else {
            $('#menuLogo').fadeOut();

            if ($mainNav.hasClass('stickytop')) {
                $mainNav.removeClass('stickytop');

                $header.css('min-height', 'auto');
                $header.css('min-height', $header.outerHeight());

                $('#logo').removeClass('hiddenLogo');

                $('header .nav-item').each(function () {
                    $(this).removeClass('stickytop')
                });

                if ($('#toc-nav').hasClass('fixed-toc'))
                    $('#toc-nav').removeClass('fixed-toc');
            }
        }
    });
});