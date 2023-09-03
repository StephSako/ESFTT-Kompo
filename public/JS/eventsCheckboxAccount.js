$(document).ready(() => {
    let spanMail = $('.parMail');
    let spanSMS = $('.parSMS');
    let niMailSMS = $('.niMailSMS');
    let encadreNotContactable = $('#encadreNotContactable');

    let i_mail = $('#i_mail');
    let i_mail2 = $('#i_mail_2');
    let i_num = $('#i_num');
    let i_num2 = $('#i_num_2');

    let cb_c_mail = $('#cb_c_mail');
    let cb_c_mail2 = $('#cb_c_mail_2');
    let cb_c_num = $('#cb_c_num');
    let cb_c_num2 = $('#cb_c_num_2');

    /** Affichage de l'encadré alertant de la non contactabilité **/
    manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, false);

    /** Evènements liés aux emails et numéros de téléphone **/
    if (!i_mail.val()) cb_c_mail.prop('checked', false).prop('disabled', true);
    if (!i_mail2.val()) cb_c_mail2.prop('checked', false).prop('disabled', true);
    if (!i_num.val()) cb_c_num.prop('checked', false).prop('disabled', true);
    if (!i_num2.val()) cb_c_num2.prop('checked', false).prop('disabled', true);

    i_mail.on('keyup', () => {
        if (!i_mail.val()) {
            cb_c_mail.prop('checked', false);
            manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
        }
        cb_c_mail.prop('disabled', !i_mail.val());
    });

    i_mail2.on('keyup', () => {
        if (!i_mail2.val()) {
            cb_c_mail2.prop('checked', false);
            manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
        }
        cb_c_mail2.prop('disabled', !i_mail2.val());
    });

    i_num.on('keyup', () => {
        if (!iphoneNumberValid(i_num.val())) {
            cb_c_num.prop('checked', false);
            manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
        }
        cb_c_num.prop('disabled', !iphoneNumberValid(i_num.val()));
    });

    i_num2.on('keyup', () => {
        if (!iphoneNumberValid(i_num2.val())) {
            cb_c_num2.prop('checked', false);
            manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
        }
        cb_c_num2.prop('disabled', !iphoneNumberValid(i_num2.val()));
    });

    cb_c_mail.on('change', () => {
        manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
    });

    cb_c_mail2.on('change', () => {
        manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
    });

    cb_c_num.on('change', () => {
        manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
    });

    cb_c_num2.on('change', () => {
        manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, true);
    });
});

function iphoneNumberValid(phoneNumber) {
    return phoneNumber.length === 10 && phoneNumber.startsWith('0');
}

function manageNotContactableEncadre(cb_c_mail, cb_c_mail2, cb_c_num, cb_c_num2, encadreNotContactable, spanMail, spanSMS, niMailSMS, editing) {
    if (editing) {
        isNotContactableMail = !(cb_c_mail.is(":checked") || cb_c_mail2.is(":checked"));
        isNotContactableSMS = !(cb_c_num.is(":checked") || cb_c_num2.is(":checked"));
    }

    if (isNotContactableMail || isNotContactableSMS) {
        encadreNotContactable.show()

        if (isNotContactableMail) spanMail.show()
        else spanMail.hide()

        if (isNotContactableSMS) spanSMS.show()
        else spanSMS.hide()

        if (isNotContactableMail && isNotContactableSMS) niMailSMS.show()
        else niMailSMS.hide()
    } else encadreNotContactable.hide()
}