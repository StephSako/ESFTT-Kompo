$(document).ready(function () {

    /** Evènements liés aux emails et numéros de téléphone **/
    if (!it_c_m.val()) cb_c_m.prop('checked', false).prop('disabled', true);
    if (!it_c_m_2.val()) cb_c_m_2.prop('checked', false).prop('disabled', true);
    if (!it_c_pn.val()) cb_c_pn.prop('checked', false).prop('disabled', true);
    if (!it_c_pn_2.val()) cb_c_pn_2.prop('checked', false).prop('disabled', true);

    it_c_m.on('keyup', function () {
        if (!it_c_m.val()) cb_c_m.prop('checked', false).prop('disabled', true);
        else cb_c_m.prop('disabled', false);
    });

    it_c_m_2.on('keyup', function () {
        if (!it_c_m_2.val()) cb_c_m_2.prop('checked', false).prop('disabled', true);
        else  cb_c_m_2.prop('disabled', false);
    });

    it_c_pn.on('keyup', function () {
        if (!it_c_pn.val()) cb_c_pn.prop('checked', false).prop('disabled', true);
        else cb_c_pn.prop('disabled', false);
    });

    it_c_pn_2.on('keyup', function () {
        if (!it_c_pn_2.val()) cb_c_pn_2.prop('checked', false).prop('disabled', true);
        else cb_c_pn_2.prop('disabled', false);
    });
});