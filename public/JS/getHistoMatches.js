function getHistoMatches(forceReloadHistoMatches = null) {
    if (!alreadyCalledHistoMatches || forceReloadHistoMatches) {
        alreadyCalledHistoMatches = true;
        if (forceReloadHistoMatches) {
            $('a.reload_histoMatches').addClass('hide');
            $('div#histoMatchesContentLoader').removeClass('hide');
            $('div#histoMatchesContent').addClass('hide');
        }
        $.ajax({
            url: '/journee/histo-matches',
            type: 'POST',
            dataType: 'json',
            success: (responseTemplate) => {
                templatingHistoMatches('#histoMatchesContent', responseTemplate, true);
            },
            error: () => {
                templatingHistoMatches('#histoMatchesContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", true);
            }
        });
    }
}

let alreadyCalledHistoMatches = false;

function templatingHistoMatches(selector, response, general = false) {
    $(selector).each(function () {
        if (general) {
            $('a.reload_histoMatches').removeClass('hide');
            $('div#histoMatchesContentLoader').addClass('hide');
            $('div#histoMatchesContent').removeClass('hide');
        }
        $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
    })
}