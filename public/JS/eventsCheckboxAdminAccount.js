function alertBecomingLoisirOrArchived(){
    if ($('#cb_loisir').is(":checked") || $('#cb_archive').is(":checked")){
        let r = confirm('Ses disponibilités seront supprimées et le joueur désélectionné de toutes compositions. Êtes-vous sûr ?');
        if (r !== true) {
            event.preventDefault();
        }
    } else return false;
}

$(document).ready(function () {
    let cb_admin = $('#cb_admin');
    let cb_capitaine = $('#cb_capitaine');
    let cb_loisir = $('#cb_loisir');
    let cb_archive = $('#cb_archive');
    let cb_entraineur = $('#cb_entraineur');

    /** Evènements liés aux checkbox Administrateur, Capitaine, Loisir, Entraineur et Archive **/
    if (cb_admin.is(":checked")) cb_archive.prop('disabled', true);

    if (cb_entraineur.is(":checked")) cb_archive.prop('disabled', true);

    if (cb_loisir.is(":checked")){
        cb_capitaine.prop('disabled', true);
        cb_archive.prop('disabled', true);
    }

    if (cb_archive.is(":checked")){
        cb_capitaine.prop('disabled', true);
        cb_admin.prop('disabled', true);
        cb_loisir.prop('disabled', true);
        cb_entraineur.prop('disabled', true);
    }

    if (cb_capitaine.is(":checked")){
        cb_loisir.prop('disabled', true);
        cb_archive.prop('disabled', true);
    }

    cb_archive.on('change', function () {
        if (!cb_archive.is(":checked")){
            cb_loisir.prop('disabled', false);
            cb_entraineur.prop('disabled', false);
            cb_capitaine.prop('disabled', false);
            cb_admin.prop('disabled', false);
        }
        else{
            cb_loisir.prop('checked', false).prop('disabled', true);
            cb_entraineur.prop('checked', false).prop('disabled', true);
            cb_capitaine.prop('checked', false).prop('disabled', true);
            cb_admin.prop('checked', false).prop('disabled', true);
        }
    });

    cb_capitaine.on('change', function () {
        if (!cb_capitaine.is(":checked")){
            cb_loisir.prop('disabled', false);
            if (!cb_entraineur.is(":checked") && !cb_admin.is(":checked")) cb_archive.prop('disabled', false);
        }
        else{
            cb_loisir.prop('disabled', true);
            cb_archive.prop('disabled', true);
        }
    });

    cb_entraineur.on('change', function () {
        if (!cb_entraineur.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked"))
            cb_archive.prop('disabled', false);
        else cb_archive.prop('disabled', true);
    });

    cb_admin.on('change', function () {
        if (!cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_entraineur.is(":checked") && !cb_loisir.is(":checked"))
            cb_archive.prop('disabled', false);
        else cb_archive.prop('disabled', true);
    });

    cb_loisir.on('change', function () {
        if (!cb_loisir.is(":checked")){
            cb_capitaine.prop('disabled', false);
            if (!cb_entraineur.is(":checked") && !cb_admin.is(":checked")) cb_archive.prop('disabled', false);
        }
        else{
            cb_capitaine.prop('checked', false).prop('disabled', true);
            cb_archive.prop('disabled', true);
        }
    });
});