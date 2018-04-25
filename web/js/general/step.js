$(function() {
    var tickets = 0;

    $(".incr-btn").each(function() {
        var $button = $(this);
    });

    function calculateTickets() {
        var q = 0;
        $('.quantity.form-control').each(function() {
            q += parseInt($(this).val());
        });
        tickets = q;
    }

    $('.quantity.form-control').on('change', function() {
        disableIfNoTickets();
    });

    function disableIfNoTickets() {
        calculateTickets();
        if(tickets == 0) {
            $('#app_bundle_contract_fan_type_submit').attr('disabled', 'disabled');
        }
        else {
            $('#app_bundle_contract_fan_type_submit').attr('disabled', null);
        }
    }

    disableIfNoTickets();


    $(".incr-btn").on("click", function (e) {
        var $button = $(this);
        var oldValue = $button.parent().find('.quantity').val();
        $button.parent().find('.incr-btn[data-action="decrease"]').removeClass('inactive');
        if ($button.data('action') == "increase") {
            if(oldValue < $button.data('max')) {
                var newVal = parseFloat(oldValue) + 1;
            }
            else {
                newVal = oldValue;
            }
        } else {
            // Don't allow decrementing below 0
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
                $button.addClass('inactive');
            }
        }
        $button.parent().find('.quantity').val(newVal);
        disableIfNoTickets();
        e.preventDefault();
    });

});

