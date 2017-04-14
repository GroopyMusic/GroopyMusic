$(document).ready(function() {

    $("#modal-dialog").dialog({
        modal: true,
        hide: 'fade',
        show: 'fade',
        autoOpen: false
    });

    /* REMOVE ALL FROM CART */
    $('button#remove-all-from-cart').on('click', function() {

        var ajax_path = $('#js-vars').data('vars').ajax_path_remove_all;

        $("#modal-dialog").html("<p>Étes-vous sûr de vouloir clear le panier ?</p><button id='confirm-remove-all-from-cart'>Confirmer</button>");

        $('#confirm-remove-all-from-cart').on('click', function() {

            $(this).hide();

            $.ajax({
                url: ajax_path,
                data: {},
                method: 'get',
                beforeSend: function(){
                    $("#modal-dialog").html("<p>Requête AJAX en cours...</p>");
                },
                success: function(data) {
                    $("#modal-dialog").html("<p>C'est fait !</p>");
                    $('#cart-content').html(data);
                },
                error: function() {
                    $("#modal-dialog").html("<p>OUPS, erreur imprévue</p>");
                }
            });
        });

    });

    /* REMOVE ONE FROM CART */
    $('button.remove-from-cart-button').on('click', function() {

        var id_purchase = $(this).attr('purchase');
        // Get Twig-defined variable for URL of AJAX call
        var ajax_path = $('#js-vars').data('vars').ajax_path_remove_one;

        $("#modal-dialog").html("<p>Étes-vous sûr de supprimer cet élément du panier ?</p>" +
            "                               <button id='confirm-delete-from-cart'>Confirmer</button>");

        $('#confirm-delete-from-cart').on('click', function () {

            $(this).hide();

            $.ajax({
                url: ajax_path,
                data: {
                    id_purchase: id_purchase
                },
                method: 'get',
                beforeSend: function () {
                    $("#modal-dialog").html("<p>Requête AJAX en cours...</p>");
                },
                success: function(data) {
                    $('#modal-dialog').html("<p>C'est fait !</p>");
                    $('#cart-content').html(data);
                },
                error: function () {
                    $('#modal-dialog').html("<p>OUPS, erreur imprévue</p>");
                }
            });
        });
    });

    /* ADD ONE TO CART */
    $('button.add-to-cart-button').on('click', function() {

        var select = $(this).prev('select');
        var quantity = select.val();
        var id_counterpart = $(this).attr('counterpart');
        var id_contract_artist = $(this).attr('contract');

        // Get Twig-defined variable for URL of AJAX call
        var ajax_path = $('#js-vars').data('vars').ajax_path;

        if (quantity > 0) {

            $("#modal-dialog").dialog('open').html("<p>Étes-vous sûr d'ajouter "+ quantity + " fois cet élément au panier ?</p>" +
                "                               <button id='confirm-add-to-cart'>Ajouter au panier</button>");

            $('#confirm-add-to-cart').on('click', function() {

                $(this).hide();

                $.ajax({
                    url: ajax_path,
                    data: {
                        id_counterpart: id_counterpart,
                        id_contract_artist: id_contract_artist,
                        quantity: quantity
                    },
                    method: 'get',
                    beforeSend: function(){
                        $("#modal-dialog").html("<p>Requête AJAX en cours...</p>");
                    },
                    success: function(response) {
                        if(response == "OK") {
                            $('#modal-dialog').html("<p>C'est fait !</p>");
                        }

                        else if(response == "MAX_QTY") {
                            $('#modal-dialog').html("<p>Impossible : quantité maximale déjà atteinte pour cet article</p>");
                        }

                        else if(response == "TO_MAX_QTY") {
                            $('#modal-dialog').html("<p>C'est fait ! (Quantité maximale atteinte pour cet article)</p>");
                        }

                        select.val(0);
                    },
                    error: function() {
                        $('#modal-dialog').html("<p>OUPS, erreur imprévue</p>");
                    }
                });
            });
        }
    });

    /* DEBLOCK SPECIAL ADVANTAGE */
    $('.deblock-advantage-button').click(function() {

        var select = $(this).prev('select');
        var quantity = parseInt(select.val());
        var id_advantage = parseInt($(this).attr('advantage'));
        var price = parseInt($(this).attr('price'));

        // Get Twig-defined variable for URL of AJAX call
        var ajax_path = $('#js-vars').data('vars').ajax_path;

        if (quantity > 0) {
            $("#modal-dialog").dialog('open').html("<p>Étes-vous sûr de débloquer " + quantity + " fois cet élément ? Cela vous coûtera " + quantity * price + " crédits </p>" +
                "                               <button id='confirm-deblock'>Confirmer</button>");
        }

        $('#confirm-deblock').click(function() {

            $(this).hide();

            $.ajax({
                url: ajax_path,
                data: {
                    id_advantage: id_advantage,
                    quantity: quantity
                },
                method: 'get',
                beforeSend: function(){
                    $("#modal-dialog").html("<p>Requête AJAX en cours...</p>");
                },
                success: function(response) {

                    if(response == "NOT_ENOUGH_CREDITS") {
                        $('#modal-dialog').html("<p>Vous n'avez pas assez de crédits pour acheter tout ça... Transaction annulée !</p>");
                    }

                    else {
                        var newNumberOfCredits = parseInt(response);
                        $('#modal-dialog').html("<p>Transaction réussie. Vous n'avez maintenant plus que " + newNumberOfCredits + " crédits bonus.</p>");
                        $('#nb_credits_fan').text(newNumberOfCredits);
                    }

                    select.val(0);
                },
                error: function() {
                    $('#modal-dialog').html("<p>OUPS, erreur imprévue</p>");
                }
            });
        });
    });

    $('a.toggleAdvantageDescription').click(function() {
        $(this).siblings('blockquote').slideToggle();
    });

    /* ---- */



});