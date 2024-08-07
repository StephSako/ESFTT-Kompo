$(document).ready(function () {
    let i_new_password = $('#new_password');
    let i_new_password_repeat = $('#new_password_validate');
    let btn_edit_password = $('#btnEditPassword');
    let i_actual_password;

    if (type === 'general') {
        i_actual_password = $('#actual_password');
        i_actual_password.on('keyup', function () {
            btn_edit_password.prop('disabled', !(i_new_password.val() && i_new_password_repeat.val() && i_actual_password.val()));
        });
    }

    i_new_password.on('keyup', function () {
        btn_edit_password.prop('disabled', !(i_new_password.val() && i_new_password_repeat.val() && (type === 'backoffice' || i_actual_password.val())));
    });

    i_new_password_repeat.on('keyup', function () {
        btn_edit_password.prop('disabled', !(i_new_password.val() && i_new_password_repeat.val() && (type === 'backoffice' || i_actual_password.val())));
    });
});