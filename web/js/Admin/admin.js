$(function () {
    $('.query_name_select').on('change', function () {
        console.log($(".query_name_select option:selected").attr('class'));
        showCorrectOptGroup('Artistes');
        console.log('hello');
    })
});

function showCorrectOptGroup(group) {
    $('.query_params_select').find('optgroup').each(function () {
        $(this).children().each(function () {
            $(this).attr('disabled', true);
            $(this).attr('hidden', true);
            console.log($(this));
        });
    })
}

