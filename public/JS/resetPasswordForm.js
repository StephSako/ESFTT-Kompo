$(document).ready(function() {
    let i_new_password = $('#new_password');
    let i_new_password_repeat = $('#new_password_validate');
    let btn_reset_password = $('#btnResetPassword');

    i_new_password.on('keyup', function () {
        if (i_new_password.val() && i_new_password_repeat.val()) btn_reset_password.prop('disabled', false);
        else btn_reset_password.prop('disabled', true);
    });

    i_new_password_repeat.on('keyup', function () {
        if (i_new_password.val() && i_new_password_repeat.val()) btn_reset_password.prop('disabled', false);
        else btn_reset_password.prop('disabled', true);
    });
});