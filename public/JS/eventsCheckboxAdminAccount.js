function alertBecomingLoisir(){
    if ($('#cb_loisir').is(":checked")){
        let r = confirm('Les disponibilités seront supprimées et le joueur désélectionné de toutes compositions. Êtes-vous sûr ?');
        if (r !== true) {
            event.preventDefault();
        }
    } else return false;
}

$(document).ready(function () {
    let cb_admin = $('#cb_admin');
    let cb_capitaine = $('#cb_capitaine');
    let cb_loisir = $('#cb_loisir');

    /** Evènements liés au checkbox Administrateur, Capitaine et Loisir **/
    if (cb_admin.is(":checked")){
        cb_capitaine.prop('checked', true).prop('disabled', true);
        cb_loisir.prop('checked', false).prop('disabled', true);
    }

    if (cb_loisir.is(":checked")){
        cb_capitaine.prop('checked', false).prop('disabled', true);
        cb_admin.prop('checked', false).prop('disabled', true);
    }

    if (cb_capitaine.is(":checked")){
        cb_loisir.prop('checked', false).prop('disabled', true);
    }

    cb_capitaine.on('change', function () {
        if (!cb_capitaine.is(":checked")) cb_loisir.prop('disabled', false);
        else cb_loisir.prop('disabled', true);
    });

    cb_admin.on('change', function () {
        if (!cb_admin.is(":checked")){
            cb_loisir.prop('disabled', true);
            cb_capitaine.prop('disabled', false);
        }
        else{
            cb_loisir.prop('checked', false).prop('disabled', true);
            cb_capitaine.prop('checked', true).prop('disabled', true);
        }
    });

    cb_loisir.on('change', function () {
        if (!cb_loisir.is(":checked")){
            cb_admin.prop('disabled', false);
            cb_capitaine.prop('disabled', false);
        }
        else{
            cb_admin.prop('checked', false).prop('disabled', true);
            cb_capitaine.prop('checked', false).prop('disabled', true);
        }
    });
});