$(function() {
    $("#modal-dialog").dialog({
        modal: true,
        hide: 'fade',
        show: 'fade',
        autoOpen: false
    });

    $('#update-motivations-button').click(function(e) {
        e.preventDefault();

        var textarea = $('#motivations-textarea');
        var motivations = textarea.val();

        // Get Twig-defined variable for URL of AJAX call
        var ajax_path = $(this).attr('href');

        $.ajax({
            url: ajax_path,
            data: {
                motivations: motivations
            },
            method: 'post',
            beforeSend: function(){
                $("#modal-dialog").dialog('open');
                $('#modal-dialog').html($('.loader')[0].outerHTML);
                $('#modal-dialog .loader').show();
            },
            success: function(response) {
                $('#modal-dialog').html('<p>Vos motivations ont bien été mises à jour.</p>');
                $('.contract-motivations').html(response);
            },
            error: function() {
                $('#modal-dialog').html("<p>OUPS, erreur imprévue</p>");
            }
        });
    });
});
