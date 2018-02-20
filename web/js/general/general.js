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

    function attach_youtube_click($video, video_id, title) {
        // Based on the YouTube ID, we can easily find the thumbnail image
        $video.css('background-image', 'url(http://i.ytimg.com/vi/' + video_id + '/hqdefault.jpg)');
        $video.append('<div class="play"><span class="youtube-caption">' + title + '</span></div>');

        $(document).delegate('#' + video_id, 'click', function () {
            // Create an iFrame with autoplay set to true
            var iframe_url = "https://www.youtube.com/embed/" + video_id + "?autoplay=1&autohide=1";
            if ($video.data('params')) iframe_url += '&' + $video.data('params');

            // The height and width of the iFrame should be the same as parent
            var iframe = $('<iframe/>', {
                'frameborder': '0',
                'src': iframe_url,
                'width': $video.width(),
                'height': $video.height()
            });

            // Replace the YouTube thumbnail with YouTube HTML5 Player
            $video.replaceWith(iframe);
        });
    }

    $(".youtube").each(function() {

        var video_id = this.id;
        var $video = $(this);

        $.getJSON("https://www.googleapis.com/youtube/v3/videos", {
            key: "AIzaSyBMt1U3tTxBt4AtRR4BAwo4knEQXJf4y-A",
            part: "snippet,statistics",
            id: video_id
        }, function(data) {
            if (data.items.length === 0) {
                attach_youtube_click($video, video_id, '');
            }

            else {
                attach_youtube_click($video, video_id, data.items[0].snippet.title);
            }
        });
    });
});

