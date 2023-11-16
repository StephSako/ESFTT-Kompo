function getGeneralClassementsVirtuels(idChampActif, forceReload = false) {
    if (!alreadyCalledClassement || forceReload) {
        alreadyCalledClassement = true;
        if (forceReload) {
            $('a.reload_ranking').addClass('hide');
            $('div#rankingContentLoader').removeClass('hide');
            $('div#rankingContent').addClass('hide');
        }
        $.ajax({
            url: '/journee/general-classement-virtuel',
            type: 'POST',
            data: {
                idChampActif: idChampActif
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementVirtuel('#rankingContent', responseTemplate, false, true);
            },
            error: () => {
                templatingClassementVirtuel('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", false, true);
            }
        });
    }
}

let alreadyCalledClassement = false;

function getPersonnalClassementVirtuel(licence, isReloadFromHistoMatches = false) {
    if (!licence) templatingClassementVirtuel('.preloader_personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Licence indéfinie</p>");
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
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementVirtuel('.personnal_virtual_rank', responseTemplate, isReloadFromHistoMatches);
            },
            error: () => {
                templatingClassementVirtuel('.personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Service FFTT indisponible</p>", isReloadFromHistoMatches);
            }
        });
    }
}

function templatingClassementVirtuel(selector, response, reloadHistoMatches = false, general = false) {
    $(selector).each(function () {
        if (general) {
            $('a.reload_ranking').removeClass('hide');
            $('div#rankingContentLoader').addClass('hide');
            $('div#rankingContent').removeClass('hide');
            $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
        } else {
            $('.preloader_personnal_virtual_rank').addClass('hide'); // On cache le préloader
            $(this).removeClass('hide').html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
        }
    })

    if (reloadHistoMatches) getHistoMatches();
}