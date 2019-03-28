$('document').ready(function() {

    "use strict"; // Start of use strict

    // Smooth scrolling using jQuery easing
    $('a.js-scroll-trigger[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: (target.offset().top - 54)
                }, 1000, "easeInOutExpo");
                return false;
            }
        }
    });

    // Closes responsive menu when a scroll trigger link is clicked
    $('.js-scroll-trigger').click(function() {
        $('.navbar-collapse').collapse('hide');
    });

    // Activate scrollspy to add active class to navbar items on scroll
    $('body').scrollspy({
        target: '#mainNav',
        offset: 56
    });

    // Collapse Navbar
    var navbarCollapse = function() {
        if ($("#mainNav").offset().top > 100) {
            $("#mainNav").addClass("navbar-shrink");
        } else {
            $("#mainNav").removeClass("navbar-shrink");
        }
    };
    // Collapse now if page is not at top
    navbarCollapse();
    // Collapse the navbar when page is scrolled
    $(window).scroll(navbarCollapse);

    $('.modal-notice').modal('show');
    $('.modal-error').modal('show');

    function rectifyQuantities($q) {
        var $displayer = $q.closest('.counterpart-form').find('.quantity-error-display');

        var max = parseInt($q.closest('.counterpart-form').find('.quantity-right-plus').data('max'));
        var quantity = parseInt($q.val());
        if (quantity > max) {
            var s = max > 1 ? 's' : '';
            $displayer.text('Vous ne pouvez pas dépasser ' + max + " exemplaire"+s+" de ce ticket dans votre commande.");
            $displayer.show();
            $q.val(max);
        }
        else if(quantity < 0) {
            $displayer.text('Vous ne pouvez pas commander un nombre négatif de tickets.');
            $displayer.show();
            $q.val(0);
        }
        else {
            $displayer.hide();
        }
        calculateTickets();
    }

    $('input.quantity').change(function() {
        rectifyQuantities($(this));
    });

    $('input.quantity').each(function() {
        rectifyQuantities($(this));
    });

    $('input.free-price-value').change(function() {
        var $fpv = $(this);
        var $displayer = $fpv.closest('.counterpart-form').find('.free-price-error-display');
        var min = parseFloat($fpv.attr('min'));
        if(parseFloat($fpv.val()) < min) {
            $displayer.text('Le prix minimum pour ce ticket est de ' + min + ' euros.');
            $displayer.show();
            $fpv.val(min);
        }
        else {
            $displayer.hide();
        }
    });

    $('.quantity-right-plus').click(function(e){
        e.preventDefault();
        var $q = $(this).closest('.input-group').find('input.quantity');
        var quantity = parseInt($q.val());
        $q.val(quantity + 1);
        $q.trigger('change');
    });

    $('.quantity-left-minus').click(function(e){
        e.preventDefault();
        var $q = $(this).closest('.input-group').find('input.quantity');
        var quantity = parseInt($q.val());
        if(quantity>0) {
            $q.val(quantity - 1);
            $q.trigger('change');
        }
    });

    function calculateTickets() {
        var tp = 0;
        var q = 0;
        $('.quantity.form-control').each(function() {
            var qval = parseFloat($(this).val());
            q += qval;
            var $priceElem = $(this).closest('.counterpart-form').find('.counterpart-price');
            var price = isNaN(parseFloat($priceElem.val())) ? parseFloat($priceElem.text()) : parseFloat($priceElem.val());
            tp += price * qval;
        });

        if(tp === 0 && q === 0) {
           $('#totals').hide();
        }

        else {
            $('#cart-total').text(tp);
            $('#quantity-total').text(q);
            $('#totals').show();
        }
    }
});
