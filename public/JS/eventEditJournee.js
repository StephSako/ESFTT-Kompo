$(document).ready(function() {
    let undefined = $('#journee_undefined');

    if (undefined.is(':checked')) $('.select-dropdown').prop('disabled', true);

    undefined.change(function() {
        if(this.checked) $('.select-dropdown').prop('disabled', true);
        else $('.select-dropdown').prop('disabled', false);
    });
});