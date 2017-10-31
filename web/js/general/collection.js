
    function um_collection(options) {
        $('.collection').collection($.extend({
            up: '',
            down: '',
            add_at_the_end: true,
            add: '<a href="#">Ajouter</a>'
        }, options));
    }
