$(document).ready(function () {

    /** Evènements liés aux emails et numéros de téléphone **/
    if (!i_mail.val()) cb_c_mail.prop('checked', false).prop('disabled', true);
    if (!i_mail2.val()) cb_c_mail2.prop('checked', false).prop('disabled', true);
    if (!i_num.val()) cb_c_num.prop('checked', false).prop('disabled', true);
    if (!i_num2.val()) cb_c_num2.prop('checked', false).prop('disabled', true);

    i_mail.on('keyup', function () {
        if (!i_mail.val()) cb_c_mail.prop('checked', false).prop('disabled', true);
        else cb_c_mail.prop('disabled', false);
    });

    i_mail2.on('keyup', function () {
        if (!i_mail2.val()) cb_c_mail2.prop('checked', false).prop('disabled', true);
        else  cb_c_mail2.prop('disabled', false);
    });

    i_num.on('keyup', function () {
        if (!i_num.val()) cb_c_num.prop('checked', false).prop('disabled', true);
        else cb_c_num.prop('disabled', false);
    });

    i_num2.on('keyup', function () {
        if (!i_num2.val()) cb_c_num2.prop('checked', false).prop('disabled', true);
        else cb_c_num2.prop('disabled', false);
    });
});