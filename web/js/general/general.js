Dropzone.autoDiscover = false;

$.fn.textWidth = function () {
    var html_org = $(this).html();
    var html_calc = '<span>' + html_org + '</span>';
    $(this).html(html_calc);
    var width = $(this).find('span:first').width();
    $(this).html(html_org);
    return width;
};

$('.rounded-title').each(function () {
    var tw = $(this).textWidth();
    $(this).width(tw + 4);
    $(this).height(tw);
    $(this).css('margin-top', '-' + $(this).outerHeight() / 2 + 'px');
    var $class = $(this).hasClass('rounded-title-login-choice') ? 'h-line h-line-login-choice' : 'h-line';
    $(this).before('<div class="' + $class + '" style="margin-top:' + $(this).outerHeight() / 2 + 'px;"></div>');
});

$(".scroll").click(function (e) {
    e.preventDefault();
    $('html, body').animate({
        scrollTop: $($(this).attr('href')).offset().top
    }, 500);
});

$(function () {

    $('[data-toggle="popover"]').popover({'html': true});
    $('[data-toggle="popover"]').on('click', function (e) {
        e.preventDefault();
        return true;
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('body').on('click', function (e) {
        //only buttons
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    $.fn.extend({
        attach_notifications_behaviour: function () {
            this.off();
            return this.each(function () {
                $(this).click(function (e) {
                    e.preventDefault();
                    $('#notifs-modal .loader').show();
                    $('#notifs-modal-content').html('');
                    $('#notifs-modal').modal('show');
                    $.get($(this).attr('href'), function (html) {
                        $('.loader').hide();
                        $('#notifs-modal-content').html(html);
                        $(this).closest('tr.notification-preview').removeClass('table-warning');
                    });
                });
            })
        }
    });

    $('.notification-trigger').attach_notifications_behaviour();

    var getBackgroundImageSize = function (el) {
        var imageUrl = el.match(/^url\(["']?(.+?)["']?\)$/);
        var dfd = new $.Deferred();

        if (imageUrl) {
            var image = new Image();
            image.onload = dfd.resolve;
            image.onerror = dfd.reject;
            image.src = imageUrl[1];
        } else {
            dfd.reject();
        }

        return dfd.then(function () {
            return {width: this.width, height: this.height};
        });
    };

    function attach_youtube_click($video, video_id, title) {
        // Based on the YouTube ID, we can easily find the thumbnail image
        var $url = "url('https://i.ytimg.com/vi/" + video_id + "/hqdefault.jpg')";
        $video.css('background-image', $url);
        var width = '100%';

        getBackgroundImageSize($url)
            .then(function (size) {
                width = Math.min(size.width, $video.outerWidth());
            })
            .always(function () {
                if (title != '') {
                    $video.append('<div class="youtube-caption">' + title + '</div>');
                }
                $video.append('<div class="play"></div>');
                $video.find('.play').css('width', width).css('bottom', $video.find('.youtube-caption').outerHeight() / 2);
                $video.find('.youtube-caption').css('width', width);
            });

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

    $(".youtube").each(function () {
        var video_id = this.id;
        var $video = $(this);

        $.getJSON("https://www.googleapis.com/youtube/v3/videos", {
            key: "AIzaSyBMt1U3tTxBt4AtRR4BAwo4knEQXJf4y-A",
            part: "snippet,statistics",
            id: video_id
        }).done(function (data) {
            if (data.items.length === 0) {
                attach_youtube_click($video, video_id, '');
            }

            else {
                attach_youtube_click($video, video_id, data.items[0].snippet.title);
            }
        })
            .fail(function () {
                attach_youtube_click($video, video_id, '');
            });
    });
});


// **** START sponsorship modal **** //
function addEmailInput() {
    var div = $('#sponsorship-modal-email-inputs-div');
    var input = '<div class="form-group"><input type="email" class="sponsorship-modal-email-inputs sponsorship-modal-added-inputs form-control" placeholder="Entrer une adresse email"></div>';
    div.append(input);
}

function removeEmailInput() {
    $('.sponsorship-modal-added-inputs').last().remove();
}

function displaySponsorshipInvitationModal() {
    $("#sponsorship-invitations-modal").on("hidden.bs.modal", function () {
        $('#sponsorship-modal-form')[0].reset();
        $('.sponsorship-modal-added-inputs').each(function () {
            $(this).remove();
        });
        hideSponsoringAlert();
    }).modal();
    $('#sponsorship-modal-form').on('submit', function (evt) {
        evt.preventDefault();
        sendSponsorshipInvitation();
    });
}

function showSponsorshipLoader() {
    $('#sponsorship-modal-content').hide();
    $('#sponsorship-modal-loader').find('.loader').first().show();
}

function hideSponsorshipLoader() {
    $('#sponsorship-modal-content').show();
    $('#sponsorship-modal-loader').find('.loader').first().hide();
}

function showSponsorshipSuccess(message) {
    var div = $('#sponsorship-modal-alert-success');
    div.append(message);
    div.attr('hidden', false)
}

function showSponsorshipDanger(message) {
    console.log("ici");
    var div = $('#sponsorship-modal-alert-danger');
    div.append(message);
    div.attr('hidden', false)
}

function hideSponsoringAlert() {
    $('#sponsorship-modal-alert-success').attr("hidden", true);
    $('#sponsorship-modal-alert-danger').attr("hidden", true);
}

function sendSponsorshipInvitation() {
    showSponsorshipLoader();
    var emails = [];
    var textarea = $('#sponsorship-modal-textarea').text();
    var url = $('#sponsorship-modal-form').attr('action');
    $('.sponsorship-modal-email-inputs').each(function () {
        emails.push($(this).val());
    });
    console.log(emails);
    $.post(url,
        {
            emails: emails,
            textarea: textarea
        }, function (result) {
            console.log(result);
            showSponsorshipSuccess(result);
            hideSponsorshipLoader();
        }
    ).fail(function (err) {
        console.log(err);
        showSponsorshipDanger(err.responseText);
        hideSponsorshipLoader();
    })
}

// **** END sponsorship modal **** //


