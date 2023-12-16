function getHistoMatches(licence, forceReloadHistoMatches) {
    if (!alreadyCalledHistoMatches || forceReloadHistoMatches) {
        alreadyCalledHistoMatches = true;
        if (forceReloadHistoMatches) {
            alreadyCalledHistoMatches = false;
            $('a.reload_histoMatches').addClass('hide');
            $('#histoMatchesContentLoader').removeClass('hide');
            $('#histoMatchesContent').addClass('hide');
            getPersonnalClassementVirtuel(licence, true);
        } else {
            $.ajax({
                url: '/journee/histo-matches',
                type: 'POST',
                data: {
                    licence: licence
                },
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
}

let alreadyCalledHistoMatches = false;

function templatingHistoMatches(selector, response, general = false) {
    $(selector).each(function () {
        if (general) {
            $('a.reload_histoMatches').removeClass('hide');
            $('#histoMatchesContentLoader').addClass('hide');
            $('#histoMatchesContent').removeClass('hide');
        }
        $(this).removeClass('hide').html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
    })
}