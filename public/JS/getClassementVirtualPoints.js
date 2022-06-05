function getGeneralClassementsVirtuels(forceReload = null) {
    if (!alreadyCalledClassement || forceReload) {
        alreadyCalledClassement = true;
        if (forceReload) {
            $('a.reload_ranking').addClass('hide');
            $('div#rankingContentLoader').removeClass('hide');
            $('span.christmas_code').removeClass('hide');
            $('div#rankingContent').addClass('hide');
        }
        $.ajax({
            url : '/journee/general-classement-virtuel',
            type : 'POST',
            dataType : 'json',
            success : function(responseTemplate) { templating('#rankingContent', responseTemplate, true); },
            error : function() { templating('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", true); }
        });
    }
}

let alreadyCalledClassement = false;

function getPersonnalClassementVirtuel() {
    $.ajax({
        url : '/journee/personnal-classement-virtuel',
        type : 'POST',
        dataType : 'json',
        success : function(responseTemplate) { templating('.preloader_personnal_virtual_rank', responseTemplate); },
        error : function() { templating('.preloader_personnal_virtual_rank', "<p style='margin-top: 10px' class='pastille reset red'>Service FFTT indisponible</p>"); }
    });
}

function templating(selector, response, general = false){
    $(selector).each(function() {
        if (general) {
            $('a.reload_ranking').removeClass('hide');
            $('div#rankingContentLoader').addClass('hide');
            $('div#rankingContent').removeClass('hide');
            $('span.christmas_code').addClass('hide');
        }
        $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
    })
}