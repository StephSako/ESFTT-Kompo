function getListeTournois() {
    // $('a.reload_ranking').addClass('hide');
    // $('div#rankingContentLoader').removeClass('hide');
    // $('div#rankingContent').addClass('hide');
    $.ajax({
        url : '/liste/tournois',
        type : 'GET',
        dataType : 'json',
        success : (responseTemplate) => { templatingListeTournois('#rankingContent', responseTemplate, true); },
        error : () => { templatingListeTournois('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>", true); }
    });
}

function templatingListeTournois(selector, response, general = false){
    console.error(response)
    // $(selector).each(function() {
    //     if (general) {
    //         $('a.reload_ranking').removeClass('hide');
    //         $('div#rankingContentLoader').addClass('hide');
    //         $('div#rankingContent').removeClass('hide');
    //     }
    //     $(this).html(response.replaceAll('chart_js_historique_id', 'chart_js_historique_id' + $(this)[0].id));
    // })
}