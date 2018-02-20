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
    }, 500);
});

$(function () {
    $('[data-toggle="popover"]').popover({'html':true});
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

    $(".youtube").each(function() {
        // Based on the YouTube ID, we can easily find the thumbnail image
        $(this).css('background-image', 'url(http://i.ytimg.com/vi/' + this.id + '/hqdefault.jpg)');

        // Overlay the Play icon to make it look like a video player
        $(this).append($('<div/>', {'class': 'play'}));

        $(document).delegate('#'+this.id, 'click', function() {
            // Create an iFrame with autoplay set to true
            var iframe_url = "https://www.youtube.com/embed/" + this.id + "?autoplay=1&autohide=1";
            if ($(this).data('params')) iframe_url+='&'+$(this).data('params');

            // The height and width of the iFrame should be the same as parent
            var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': $(this).width(), 'height': $(this).height() })

            // Replace the YouTube thumbnail with YouTube HTML5 Player
            $(this).replaceWith(iframe);
        });
    });
});

