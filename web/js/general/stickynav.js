var $header = $('header');
var $mainNav = $('#mainNav');
var $main = $('main');
var $footer = $('footer');

var stickyoffset = $main.offset().top - $mainNav.outerHeight();

function mainPadding() {
    if($header.outerHeight() == 0) {
        $main.css('margin-top', $mainNav.outerHeight());
    }
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


function onResize() {
    mainPadding();
    footerPosition();
}

$(function() {
    onResize();

    $(window).resize(function () {
        onResize();
    });

    $('.navbar-collapse').on('hidden.bs.collapse', function() {
        onResize();
    });

});