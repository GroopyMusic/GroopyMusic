var artists = null;
var contractArtists = null;
var counterParts = null;
var steps = null;

$(function () {
    $('.query_name_select').on('change', function () {
        getOptions();
        showCorrectOptGroup($(".query_name_select option:selected").attr('class'));
        console.log('hello');
    })
});

function showCorrectOptGroup(group) {
    console.log(group);
    var select = $('.query_params_select');
    console.log(select);
    select.val(null).trigger("change");
    switch (group) {
        case 'null-type':
            break;
        case 'contractArtist-type':
            select.last().append(contractArtists);
            console.log(contractArtists);
            break;
        case 'artist-type':
            select.last().append(artists);
            console.log(artists);
            break;
        case 'counterPart-type' :
            select.last().append(counterParts);
            console.log(counterParts);
            break;
        case 'step-type' :
            select.last().append(steps);
            console.log(steps);
            break;
    }
    $(select).trigger('change');
}

function getOptions() {
    $('.query_params_select').find('optgroup').each(function () {
        var optgroup = $(this);
        if (optgroup.attr('label') === 'Artistes') {
            artists = $(this);
        }
        if (optgroup.attr('label') === 'Evénements') {
            contractArtists = $(this);
        }
        if (optgroup.attr('label') === 'Contre parties') {
            counterParts = $(this);
        }
        if (optgroup.attr('label') === 'Paliers de salle') {
            steps = $(this);
        }
        $(this).remove();
    });
}

