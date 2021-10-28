function getLastComposAdversaire(nomAdversaire, lienDivision, numeroEquipe) {
    $.ajax({
        url : '/journee/last_compos_adversaire',
        type : 'POST',
        data: {
            nomAdversaire: nomAdversaire,
            lienDivision: lienDivision /** Propriété d'Equipe pour récupérer le classement de la poule pour avoir les numéros des clubs */
        },
        dataType : 'json',
        success : function(responseTemplate) { templating(numeroEquipe, responseTemplate); },
        error : function() { templating(numeroEquipe, "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>"); }
    });
}

function templating(numeroEquipe, response){
    $('#joueursAdv' + numeroEquipe).html(response);
}