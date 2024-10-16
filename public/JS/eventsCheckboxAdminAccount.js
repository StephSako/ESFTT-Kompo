function alertBecomingLoisirOrArchived() {
    let becomingLoisir = $('#cb_loisir').is(":checked") && !isLoisir && !isArchived;
    let becomingArchive = $('#cb_archive').is(":checked") && !isArchived;
    if (becomingLoisir || becomingArchive) {
        let r = confirm('Membre déclaré ' + (becomingArchive ? 'archivé' : 'loisir') + (!isCompetiteur ? "." : " : ses disponibilités seront supprimées et il sera désélectionné de toutes compositions ultèrieures à aujourd'hui inclues.") + " Êtes-vous sûr ?");
        if (r !== true) event.preventDefault();
    } else return false;
}

$(document).ready(() => {
    let cb_admin = $('#cb_admin');
    let cb_capitaine = $('#cb_capitaine');
    let cb_loisir = $('#cb_loisir');
    let cb_jeune = $('#cb_jeune');
    let cb_competiteur = $('#cb_competiteur');
    let cb_archive = $('#cb_archive');
    let cb_entraineur = $('#cb_entraineur')
    let cb_crit_fed = $('#cb_crit_fed');
    let btn_modifier = $('#btn_modifier');
    let form_equipes_associees = $('#equipesAssocieesForm');

    /** Evènements liés aux checkbox Administrateur, Capitaine, Loisir, Entraineur et Archive **/
    if (cb_admin.is(":checked")) cb_archive.prop('checked', false).prop('disabled', true);

    if (cb_entraineur.is(":checked")) cb_archive.prop('checked', false).prop('disabled', true);

    if (cb_loisir.is(":checked")) {
        cb_capitaine.prop('checked', false).prop('disabled', true);
        cb_archive.prop('checked', false).prop('disabled', true);
        cb_competiteur.prop('checked', false).prop('disabled', true);
    }

    if (cb_archive.is(":checked")) {
        cb_capitaine.prop('checked', false).prop('disabled', true);
        cb_admin.prop('checked', false).prop('disabled', true);
        cb_loisir.prop('checked', false).prop('disabled', true);
        cb_entraineur.prop('checked', false).prop('disabled', true);
        cb_competiteur.prop('checked', false).prop('disabled', true);
        cb_jeune.prop('checked', false).prop('disabled', true);
    }

    if (cb_competiteur.is(":checked")) {
        form_equipes_associees.removeClass('hide');
        cb_loisir.prop('checked', false).prop('disabled', true);
        cb_archive.prop('checked', false).prop('disabled', true);
    } else {
        cb_crit_fed.prop('checked', false).prop('disabled', true);
    }

    cb_archive.on('change', (e) => {
        btn_modifier.prop('disabled', type === 'backoffice' && !cb_archive.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_entraineur.is(":checked") && !cb_loisir.is(":checked") && !cb_competiteur.is(":checked"));
        cb_loisir.prop('disabled', e.currentTarget.checked);
        cb_entraineur.prop('disabled', e.currentTarget.checked);
        cb_admin.prop('disabled', e.currentTarget.checked);
        cb_competiteur.prop('disabled', e.currentTarget.checked);
        cb_capitaine.prop('disabled', e.currentTarget.checked);
        cb_crit_fed.prop('disabled', true);
        cb_jeune.prop('disabled', e.currentTarget.checked);
    });

    cb_capitaine.on('change', () => {
        if (!cb_capitaine.is(":checked")) {
            if (type === 'backoffice' && !cb_admin.is(":checked") && !cb_entraineur.is(":checked") && !cb_loisir.is(":checked") && !cb_competiteur.is(":checked") && !cb_archive.is(":checked"))
                btn_modifier.prop('disabled', true);
            if (!cb_competiteur.is(':checked')) cb_loisir.prop('disabled', false);
            if (!cb_entraineur.is(":checked") && !cb_admin.is(":checked") && !cb_competiteur.is(':checked'))
                cb_archive.prop('disabled', false);
        } else {
            btn_modifier.prop('disabled', false);
            cb_loisir.prop('disabled', true);
            cb_archive.prop('disabled', true);
            cb_competiteur.prop('disabled', false);
        }
    });

    cb_entraineur.on('change', () => {
        if (!cb_jeune.is(":checked") && !cb_entraineur.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_competiteur.is(":checked") && !cb_loisir.is(":checked")) {
            if (type === 'backoffice' && !cb_archive.is(":checked") && !cb_loisir.is(":checked")) btn_modifier.prop('disabled', true);
            cb_archive.prop('disabled', false);
        } else {
            btn_modifier.prop('disabled', false);
            cb_archive.prop('disabled', true);
        }
    });

    cb_jeune.on('change', () => {
        if (!cb_jeune.is(":checked") && !cb_entraineur.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_competiteur.is(":checked") && !cb_loisir.is(":checked")) {
            cb_archive.prop('disabled', false);
        } else {
            cb_archive.prop('disabled', true);
        }
    });

    cb_admin.on('change', () => {
        if (!cb_jeune.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_entraineur.is(":checked") && !cb_loisir.is(":checked") && !cb_competiteur.is(":checked")) {
            if (type === 'backoffice' && !cb_archive.is(":checked")) btn_modifier.prop('disabled', true);
            cb_archive.prop('disabled', false);
        } else {
            btn_modifier.prop('disabled', false);
            cb_archive.prop('disabled', true);
        }
    });

    cb_loisir.on('change', (e) => {
        if (!cb_loisir.is(":checked")) {
            if (type === 'backoffice' && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_entraineur.is(":checked") && !cb_competiteur.is(":checked") && !cb_archive.is(":checked"))
                btn_modifier.prop('disabled', true);
            if (!cb_jeune.is(":checked") && !cb_entraineur.is(":checked") && !cb_admin.is(":checked")) cb_archive.prop('disabled', false);
        } else {
            btn_modifier.prop('disabled', false);
            cb_capitaine.prop('disabled', true);
            cb_archive.prop('disabled', true);
        }
        cb_competiteur.prop('disabled', e.currentTarget.checked);
    });

    cb_competiteur.on('change', (e) => {
        if (!cb_competiteur.is(":checked")) {
            form_equipes_associees.addClass('hide');
            if (type === 'backoffice' && !cb_admin.is(":checked") && !cb_capitaine.is(":checked") && !cb_entraineur.is(":checked") && !cb_loisir.is(":checked") && !cb_archive.is(":checked"))
                btn_modifier.prop('disabled', true);
            if (cb_capitaine.is(":checked")) cb_capitaine.prop('checked', false)
            if (!cb_jeune.is(":checked") && !cb_entraineur.is(":checked") && !cb_admin.is(":checked") && !cb_capitaine.is(":checked")) cb_archive.prop('disabled', false);
        } else {
            form_equipes_associees.removeClass('hide');
            btn_modifier.prop('disabled', false);
            cb_archive.prop('disabled', true);
        }
        cb_crit_fed.prop('checked', false).prop('disabled', !e.currentTarget.checked);
        cb_capitaine.prop('disabled', !e.currentTarget.checked);
        cb_loisir.prop('disabled', e.currentTarget.checked);
    });

});