function getListeTournois() {
    $.ajax({
        url: '/liste/tournois',
        type: 'GET',
        dataType: 'json',
        success: (responseTemplate) => {
            templatingListeTournois('#rankingContent', responseTemplate);
        },
        error: () => {
            templatingListeTournois('#rankingContent', "<p style='margin: 8px auto' class='pastille reset red'>Le service de l'Espace MonClub rencontre des perturbations. Réessayez plus tard</p>");
        }
    });
}

function templatingListeTournois(selector, response) {
    $('#listTournois').removeClass('hide').html(response);
    $('.collapsible').collapsible({
        accordion: false
    });
}