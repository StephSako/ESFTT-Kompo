$(document).ready(() => {
    let i_new_password = $('#new_password');
    let i_new_password_repeat = $('#new_password_validate');
    let btn_reset_password = $('#btnResetPassword');

    i_new_password.on('keyup', () => {
        btn_reset_password.prop('disabled', !(i_new_password.val() && i_new_password_repeat.val()));
    });

    i_new_password_repeat.on('keyup', () => {
        btn_reset_password.prop('disabled', !(i_new_password.val() && i_new_password_repeat.val()));
    });
});