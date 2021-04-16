$(document).ready(function() {
    let i_new_password = $('#new_password');
    let i_new_password_repeat = $('#new_password_validate');
    let btn_edit_password = $('#btnEditPassword');

    let span_passwords_not_matching = $('#span_passwords_not_matching');
    let span_passwords_matching = $('#span_passwords_matching');

    btn_edit_password.prop('disabled', true);

    i_new_password.on('keyup', function () {
        if (i_new_password.val() && i_new_password_repeat.val()){
            btn_edit_password.prop('disabled', false);
            if (i_new_password.val() === i_new_password_repeat.val()){
                span_passwords_matching.removeAttr('hidden');
                span_passwords_not_matching.attr('hidden', true);
            } else {
                span_passwords_not_matching.removeAttr('hidden');
                span_passwords_matching.attr('hidden', true);
            }
        }
        else{
            span_passwords_matching.attr('hidden', true);
            span_passwords_not_matching.attr('hidden', true);
            btn_edit_password.prop('disabled', true);
        }
    });

    i_new_password_repeat.on('keyup', function () {
        if (i_new_password.val() && i_new_password_repeat.val()){
            btn_edit_password.prop('disabled', false);
            if (i_new_password.val() === i_new_password_repeat.val()){
                span_passwords_matching.removeAttr('hidden');
                span_passwords_not_matching.attr('hidden', true);
            } else {
                span_passwords_not_matching.removeAttr('hidden');
                span_passwords_matching.attr('hidden', true);
            }
        }
        else{
            span_passwords_matching.attr('hidden', true);
            span_passwords_not_matching.attr('hidden', true);
            btn_edit_password.prop('disabled', true);
        }
    });
});