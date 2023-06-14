function getListeTournois() {
    $.ajax({
        url : '/liste/tournois',
        type : 'GET',
        dataType : 'json',
        success : (responseTemplate) => { templatingListeTournois('#rankingContent', responseTemplate); },
        error : () => { templatingListeTournois('#rankingContent', "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>"); }
    });
}

function templatingListeTournois(selector, response){
    // $('div#loader').addClass('hide');
    // console.error(response)
    $('#listTournois').removeClass('hide');
    $('#listTournois').html(response);

    $('.collapsible').collapsible({
        accordion : false
    });
}