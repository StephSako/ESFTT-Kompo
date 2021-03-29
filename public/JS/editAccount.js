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

    /** Evènements liés au checkbox Administrateur, Capitaine et Visiteur **/
    if (cb_admin.is(":checked")){
        cb_capitaine.prop('checked', true).prop('disabled', true);
        cb_visiteur.prop('checked', false).prop('disabled', true);
    }

    if (cb_visiteur.is(":checked")){
        cb_capitaine.prop('checked', false).prop('disabled', true);
        cb_admin.prop('checked', false).prop('disabled', true);
    }

    if (cb_capitaine.is(":checked")){
        cb_visiteur.prop('checked', false).prop('disabled', true);
    }

    cb_capitaine.on('change', function () {
        if (!cb_capitaine.is(":checked")) cb_visiteur.prop('disabled', false);
        else cb_visiteur.prop('disabled', true);
    });

    cb_admin.on('change', function () {
        if (!cb_admin.is(":checked")){
            cb_visiteur.prop('disabled', true);
            cb_capitaine.prop('disabled', false);
        }
        else{
            cb_visiteur.prop('checked', false).prop('disabled', true);
            cb_capitaine.prop('checked', true).prop('disabled', true);
        }
    });

    cb_visiteur.on('change', function () {
        if (!cb_visiteur.is(":checked")){
            cb_admin.prop('disabled', false);
            cb_capitaine.prop('disabled', false);
        }
        else{
            cb_admin.prop('checked', false).prop('disabled', true);
            cb_capitaine.prop('checked', false).prop('disabled', true);
        }
    });
});