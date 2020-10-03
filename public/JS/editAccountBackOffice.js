$(document).ready(function () {
    let it_c_m = $('#backoffice_competiteur_mail');
    let it_c_m_2 = $('#backoffice_competiteur_mail2');
    let it_c_pn = $('#backoffice_competiteur_phoneNumber');
    let it_c_pn_2 = $('#backoffice_competiteur_phoneNumber2');

    let cb_c_m = $('#backoffice_competiteur_contactableMail');
    let cb_c_m_2 = $('#backoffice_competiteur_contactableMail2');
    let cb_c_pn = $('#backoffice_competiteur_contactablePhoneNumber');
    let cb_c_pn_2 = $('#backoffice_competiteur_contactablePhoneNumber2');

    if (!it_c_m.val()) cb_c_m.prop('checked', false).prop('disabled', true);
    if (!it_c_m_2.val()) cb_c_m_2.prop('checked', false).prop('disabled', true);
    if (!it_c_pn.val()) cb_c_pn.prop('checked', false).prop('disabled', true);
    if (!it_c_pn_2.val()) cb_c_pn_2.prop('checked', false).prop('disabled', true);

    it_c_m.on('keyup', function () {
        console.log(it_c_m.val())
        if (!it_c_m.val()) cb_c_m.prop('checked', false).prop('disabled', true);
        else cb_c_m.prop('disabled', false);
    });

    it_c_m_2.on('keyup', function () {
        console.log(it_c_m_2.val())
        if (!it_c_m_2.val()) cb_c_m_2.prop('checked', false).prop('disabled', true);
        else  cb_c_m_2.prop('disabled', false);
    });

    it_c_pn.on('keyup', function () {
        console.log(it_c_pn.val())
        if (!it_c_pn.val()) cb_c_pn.prop('checked', false).prop('disabled', true);
        else cb_c_pn.prop('disabled', false);
    });

    it_c_pn_2.on('keyup', function () {
        console.log(it_c_pn_2.val())
        if (!it_c_pn_2.val()) cb_c_pn_2.prop('checked', false).prop('disabled', true);
        else cb_c_pn_2.prop('disabled', false);
    });
});