$.fn.textWidth = function(){
    var html_org = $(this).html();
    var html_calc = '<span>' + html_org + '</span>';
    $(this).html(html_calc);
    var width = $(this).find('span:first').width();
    $(this).html(html_org);
    return width;
};

$('.rounded-title').each(function() {
    var tw = $(this).textWidth();
    $(this).width(tw + 4);
    $(this).height(tw);
    $(this).css('margin-top', '-' + $(this).outerHeight()/2 + 'px');
    $(this).before('<div class="h-line" style="margin-top:'+ $(this).outerHeight()/2 +'px;"></div>');
});

$(".scroll").click(function(e) {
    e.preventDefault();
    $('html, body').animate({
        scrollTop: $($(this).attr('href')).offset().top
    }, 1000);
});
