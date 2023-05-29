function getGeneralClassementsVirtuels(forceReload = null) {
    if (!alreadyCalledClassement || forceReload) {
        alreadyCalledClassement = true;
        if (forceReload) {
            $('a.reload_ranking').addClass('hide');
            $('div#rankingContentLoader').removeClass('hide');
            $('div#rankingContent').addClass('hide');
        }
        $.ajax({
            url : '/journee/general-classement-virtuel',
            type : 'POST',
            dataType : 'json',
            success : (responseTemplate) => { templatingClassementVirtuel('#rankingContent', responseTemplate, true); },
            error : () => { templatingClassementVirtuel('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", true); }
        });
    }
}

let alreadyCalledClassement = false;

function getPersonnalClassementVirtuel(licence) {
    if (!licence) templatingClassementVirtuel('.preloader_personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Licence indéfinie</p>");
    else {
        $.ajax({
            url : '/journee/personnal-classement-virtuel',
            type : 'POST',
            dataType : 'json',
            success : (responseTemplate) => { templatingClassementVirtuel('.preloader_personnal_virtual_rank', responseTemplate); },
            error : () => { templatingClassementVirtuel('.preloader_personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Service FFTT indisponible</p>"); }
        });
    }
}

function templatingClassementVirtuel(selector, response, general = false){
    $(selector).each(function() {
        if (general) {
            $('a.reload_ranking').removeClass('hide');
            $('div#rankingContentLoader').addClass('hide');
            $('div#rankingContent').removeClass('hide');
        }
        $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
    })
}