var send_url;
$(document).ready(function () {
    console.log("ready!");
});

function addEmailInput() {
    var div = $('#sponsorship-modal-email-inputs-div');
    var placeholder = $('.sponsorship-modal-email-inputs').first().attr('placeholder');
    console.log(placeholder);
    var input = '<div class="form-group"><input type="email" class="sponsorship-modal-email-inputs sponsorship-modal-added-inputs form-control" placeholder="' + placeholder + '"required></div>';
    console.log(input);
    div.append(input);
}

function removeEmailInput() {
    $('.sponsorship-modal-added-inputs').last().remove();
}

function clearForm() {
    $('#sponsorship-modal-form')[0].reset();
    $('.sponsorship-modal-added-inputs').each(function () {
        $(this).remove();
    });
}

function displaySponsorshipInvitationModal(url) {
    send_url = url;
    $("#sponsorship-invitations-modal").on("hidden.bs.modal", function () {
        clearForm();
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
    $('#sponsorship-modal-send-button').attr('disabled', true);
}

function hideSponsorshipLoader() {
    $('#sponsorship-modal-content').show();
    $('#sponsorship-modal-loader').find('.loader').first().hide();
    $('#sponsorship-modal-send-button').attr('disabled', false);
}

function showSponsorshipSuccess(message) {
    var div = $('#sponsorship-modal-alert-success');
    div.append(message);
    div.attr('hidden', false)
}

function showSponsorshipDanger(message) {
    var div = $('#sponsorship-modal-alert-danger');
    div.append(message);
    div.attr('hidden', false)
}

function showSponsorshipWarning(emails, message) {
    var div = $('#sponsorship-modal-alert-warning');
    var list = $('<ul/>');
    emails.forEach(function (elem) {
        list.append(('<li>' + elem + '</li>'));
    });
    div.append(message);
    div.append(list);
    div.attr('hidden', false)
}

function hideSponsoringAlert() {
    $('#sponsorship-modal-alert-success').attr("hidden", true).text('').children().remove();
    $('#sponsorship-modal-alert-danger').attr("hidden", true).text('').children().remove();
    $('#sponsorship-modal-alert-warning').attr("hidden", true).text('').children().remove();
}

function sendSponsorshipInvitation() {
    console.log("hello");
    showSponsorshipLoader();
    hideSponsoringAlert();
    var emails = [];
    var textarea = $('#sponsorship-modal-textarea').val();
    console.log(send_url);
    $('.sponsorship-modal-email-inputs').each(function () {
        emails.push($(this).val());
    });
    $.post(send_url,
        {
            emails: emails,
            content: textarea
        }, function (result) {
            console.log(result);
            console.log(result.success);
            if (result.success === true) {
                showSponsorshipSuccess(result.message);
            }
            if (result.emails.length > 0) {
                showSponsorshipWarning(result.emails, result.warning_message);
            }
            hideSponsorshipLoader();
            clearForm();
        }
    ).fail(function (err) {
        console.log(err);
        showSponsorshipDanger(err.responseText);
        hideSponsorshipLoader();
        clearForm();
    })
}