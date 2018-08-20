$(function() {
    var tickets = 0;
    var totalprice = 0;
    var needForTotal = $('#cart-total').length;

    function calculateTickets() {
        var tp = 0;
        var q = 0;
        $('.quantity.form-control').each(function() {
            var qval = parseInt($(this).val());
            q += qval;
            var $select2_container = $(this).closest('.counterpart-form').find('.select2-artists');
            if(qval == 0) {
                $select2_container.hide();
            }
            else {
                if(needForTotal) {
                    var price = parseInt($(this).closest('.count-input').attr('data-price'));
                    tp += price * qval;
                }
                $select2_container.show();
            }
        });
        tickets = q;
        totalprice = tp;
        if(needForTotal) {
            $('#cart-total').text(totalprice);
        }
    }

    $('.quantity.form-control').on('change', function() {
        disableIfNoTickets();
    });

    function disableIfNoTickets() {
        calculateTickets();
        if(tickets == 0) {
            $('.submit-cart').attr('disabled', 'disabled');
        }
        else {
            $('.submit-cart').attr('disabled', null);
        }
    }

    disableIfNoTickets();

    $(".incr-btn").on("click", function (e) {
        var $button = $(this);
        var newVal;
        var oldValue = $button.parent().find('.quantity').val();
        $button.parent().find('.incr-btn[data-action="decrease"]').removeClass('inactive');
        if ($button.data('action') == "increase") {
            if(oldValue < $button.data('max')) {
                newVal = parseFloat(oldValue) + 1;
            }
            else {
                newVal = oldValue;
            }
        } else {
            // Don't allow decrementing below 0
            if (oldValue > 0) {
                newVal = parseFloat(oldValue) - 1;
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

