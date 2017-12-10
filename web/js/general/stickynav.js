var $header = $('header');
var $mainNav = $('#mainNav');
var $main = $('main');
var $footer = $('footer');
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

function footerPosition() {
    $footer.css('margin-top', 'initial');

    var docHeight = $(window).height();
    var footerHeight = $footer.outerHeight();
    var footerTop = $footer.position().top + footerHeight;

    if (footerTop < docHeight) {
        $footer.css('margin-top', (docHeight - footerTop) + 'px');
    }
}

function resizeHeader() {
    $header.css('min-height', 'auto');
    $header.css('min-height', $header.outerHeight());
}

function onResize() {
    mainPadding();
    mainNavHeight();
    footerPosition();
    resizeHeader();
}

$(function() {
    onResize();

    $(window).resize(function () {
        onResize();
    });

    $('.navbar-collapse').on('hidden.bs.collapse', function() {
        onResize();
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > stickyoffset) {
            if (!$mainNav.hasClass('stickytop')) {
                $mainNav.addClass('stickytop');

                $('#menuLogo').fadeIn();

                $('header .nav-item').each(function () {
                    $(this).addClass('stickytop')
                });


                $('#logo').addClass('hiddenLogo');
                stickyoffset = $main.offset().top - $mainNav.outerHeight();

                // Bug fix for logo appearing once too quickly
               // if(appearances > 0 || $(this).scrollTop() > stickyoffset + 10)
            }
            if (!$('#static-toc').hasClass('fixed-toc'))
                $('#static-toc').addClass('fixed-toc');
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
        onResize();
    });
});