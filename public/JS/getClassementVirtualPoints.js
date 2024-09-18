function getGeneralClassementsVirtuels(idChampActif, forceReload) {
    if (!alreadyCalledClassement || forceReload) {
        alreadyCalledClassement = true;
        if (forceReload) {
            $('a.reload_ranking').addClass('hide');
            $('#rankingContentLoader').removeClass('hide');
            $('#rankingContent').addClass('hide');
        }
        $.ajax({
            url: '/journee/general-classement-virtuel',
            type: 'POST',
            data: {
                idChampActif: idChampActif
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementVirtuel('#rankingContent', responseTemplate, '#rankingContentLoader', null, false, true);

                // On met à jour les progressions par équipe si le tableau a déjà été chargé
                if (alreadyCalledClassementEquipes) getEquipesClassementsVirtuels(idChampActif, true)
            },
            error: () => {
                templatingClassementVirtuel('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", '#rankingContentLoader', null, false, true);
            }
        });
    }
}

function getEquipesClassementsVirtuels(idChampActif, forceReload) {
    if (!alreadyCalledClassementEquipes || forceReload) {
        alreadyCalledClassementEquipes = true;
        if (forceReload) {
            $('a.reload_ranking').addClass('hide');
            $('#preloaderProgressionsEquipes').removeClass('hide');
            $('#progressionsEquipes').addClass('hide');
        }
        $.ajax({
            url: '/journee/equipes-classement-virtuel',
            type: 'POST',
            data: {
                idChampActif: idChampActif
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementVirtuel('#progressionsEquipes', responseTemplate, '#preloaderProgressionsEquipes', null, false, true);
            },
            error: () => {
                templatingClassementVirtuel('#progressionsEquipes', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", '#preloaderProgressionsEquipes', null, false, true);
            }
        });
    }
}

let alreadyCalledClassement = false;
let alreadyCalledClassementEquipes = false;

function getPersonnalClassementVirtuel(licence, isReloadFromHistoMatches) {
    if (!licence) templatingClassementVirtuel('.personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Licence indéfinie</p>", '.preloader_personnal_virtual_rank', licence, false, false);
    else {
        if (isReloadFromHistoMatches) {
            $('.personnal_virtual_rank').each(function () {
                $('.preloader_personnal_virtual_rank').removeClass('hide');
                $(this).addClass('hide')
            })
        }
        $.ajax({
            url: '/journee/personnal-classement-virtuel',
            type: 'POST',
            data: {
                licence: licence,
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementVirtuel('.personnal_virtual_rank', responseTemplate, '.preloader_personnal_virtual_rank', licence, isReloadFromHistoMatches, false);
            },
            error: () => {
                templatingClassementVirtuel('.personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Service FFTT indisponible</p>", '.preloader_personnal_virtual_rank', licence, isReloadFromHistoMatches, false);
            }
        });
    }
}

function templatingClassementVirtuel(selector, response, preloader, licence, reloadHistoMatches, general) {
    $(selector).each(function () {
        if (general) {
            $('a.reload_ranking').removeClass('hide');
            $(preloader).addClass('hide');
            $(selector).removeClass('hide');
            $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
        } else {
            $(preloader).addClass('hide');
            $(this).removeClass('hide').html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
        }
    })

    if (reloadHistoMatches && licence) getHistoMatches(licence, false, false);
}