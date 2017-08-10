/* DEBLOCK SPECIAL ADVANTAGE */

$("#modal-dialog").dialog({
    modal: true,
    hide: 'fade',
    show: 'fade',
    autoOpen: false
});

$('#update-motivations-button').click(function() {

    var textarea = $('#motivations-textarea');
    var motivations = textarea.val();
    var id_contract = parseInt($(this).attr('contract'));

    // Get Twig-defined variable for URL of AJAX call
    var ajax_path = $('#js-vars').data('vars').ajax_path_update_motivations;

    $.ajax({
        url: ajax_path,
        data: {
            id_contract: id_contract,
            motivations: motivations
        },
        method: 'post',
        beforeSend: function(){
            $("#modal-dialog").dialog('open').html("<p>Requête AJAX en cours...</p>");
        },
        success: function(response) {
            $('#modal-dialog').html('<p>Réussi</p>')
        },
        error: function() {
            $('#modal-dialog').html("<p>OUPS, erreur imprévue</p>");
        }
    });
});