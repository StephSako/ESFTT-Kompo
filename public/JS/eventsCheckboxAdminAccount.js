function alertBecomingVisitor(){
    if ($('#cb_visiteur').is(":checked")){
        let r = confirm('Les disponiblités seront supprimées et le joueur désélectionné de toutes compositions. Êtes-vous sûr ?');
        if (r !== true) {
            event.preventDefault();
        }
    } else return false;
}

$(document).ready(function () {
    let cb_admin = $('#cb_admin');
    let cb_capitaine = $('#cb_capitaine');
    let cb_visiteur = $('#cb_visiteur');

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