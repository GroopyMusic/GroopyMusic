var contract_id = null;
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

function displaySponsorshipInvitationModal(id, payment) {
    console.log(id);
    contract_id = id;
    $("#sponsorship-invitations-modal").on("hidden.bs.modal", function () {
        clearForm();
        hideSponsoringAlert();
    }).modal();
    $('#sponsorship-modal-form').on('submit', function (evt) {
        evt.preventDefault();
        sendSponsorshipInvitation();
    });
    if (payment) {
        $('#sponsorship-modal-alert-success-payment').attr("hidden", false);
    }
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

function showSponsorshipSuccess() {
    $('#sponsorship-modal-alert-success').attr('hidden', false);
}

function showSponsorshipDanger(message) {
    var div = $('#sponsorship-modal-alert-danger');
    div.append(message);
    div.attr('hidden', false)
}

function showSponsorshipWarning(emails, message) {
    $('#sponsorship-modal-alert-warning').attr('hidden', false);
}

function hideSponsoringAlert() {
    $('#sponsorship-modal-alert-success').attr("hidden", true);
    $('#sponsorship-modal-alert-danger').attr("hidden", true).text('');
    $('#sponsorship-modal-alert-warning').attr("hidden", true).text('');
    $('#sponsorship-modal-alert-success-payment').attr("hidden", true);
}

function sendSponsorshipInvitation() {
    console.log("hello");
    showSponsorshipLoader();
    hideSponsoringAlert();
    var defined = true;
    var emails = [];
    var url = $('#sponsorship-modal-form').attr('action');
    var textarea = $('#sponsorship-modal-textarea').val();
    if (contract_id === null) {
        contract_id = $('#sponsorship-modal-select').find(":selected").attr('id');
        defined = false;
    }
    $('.sponsorship-modal-email-inputs').each(function () {
        emails.push($(this).val());
    });
    $.post(url,
        {
            emails: emails,
            content: textarea,
            contractArtist: contract_id,
            defined: defined
        }, function (html) {
            $('#sponsorship-invitations-modal').on("hidden.bs.modal", function () {
                $('#sponsorship-invitations-modal').replaceWith(html);
            }).modal('hide');
            displaySponsorshipInvitationModal(contract_id);
            showSponsorshipSuccess();
            showSponsorshipWarning();
            hideSponsorshipLoader();
        }
    ).fail(function (err) {
        showSponsorshipDanger(err.responseText);
        hideSponsorshipLoader();
        clearForm();
    })
}