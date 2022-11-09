$(document).ready(() => {
    let journee_undefined = $('#journee_undefined');

    if (journee_undefined.is(':checked')) $('.select-dropdown').prop('disabled', true);

    journee_undefined.change(() => {
        if(this.checked) $('.select-dropdown').prop('disabled', true);
        else $('.select-dropdown').prop('disabled', false);
    });
});