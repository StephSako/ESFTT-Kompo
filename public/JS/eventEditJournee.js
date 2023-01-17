$(document).ready(() => {
    let journee_undefined = $('#journee_undefined');

    if (journee_undefined.is(':checked')) $('.select-dropdown').prop('disabled', true);

    journee_undefined.change((e) => {
        $('.select-dropdown').prop('disabled', e.currentTarget.checked);
    });
});