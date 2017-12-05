Dropzone.autoDiscover = false;

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

$(function () {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="popover"]').on('click', function(e) {e.preventDefault(); return true;});

    $('[data-toggle="tooltip"]').tooltip();

    $('body').on('click', function (e) {
        //only buttons
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    $.fn.extend({
        attach_notifications_behaviour: function() {
            this.off();
            return this.each(function() {
                $(this).click(function(e) {
                    e.preventDefault();
                    $('#notifs-modal .loader').show();
                    $('#notifs-modal-content').html('');
                    $('#notifs-modal').modal('show');
                    $.get($(this).attr('href'), function(html) {
                        $('.loader').hide();
                        $('#notifs-modal-content').html(html);
                        $(this).closest('tr.notification-preview').removeClass('table-warning');
                    });
                });
            })
        }
    });

    $('.notification-trigger').attach_notifications_behaviour();


});

