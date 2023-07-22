function getLastComposAdversaire(nomAdversaire, lienDivision, numeroEquipe) {
    if (!alreadyCalledComposAdversaires.includes(numeroEquipe)) {
        alreadyCalledComposAdversaires.push(numeroEquipe);
        $.ajax({
            url: '/journee/last-compos-adversaire',
            type: 'POST',
            data: {
                nomAdversaire: nomAdversaire,
                lienDivision: lienDivision /** Propriété d'Equipe pour récupérer le classement de la poule pour avoir les numéros des clubs */
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingLastComposAdversaire(numeroEquipe, responseTemplate);
            },
            error: () => {
                templatingLastComposAdversaire(numeroEquipe, "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>");
            }
        });
    }
}

function templatingLastComposAdversaire(numeroEquipe, response) {
    $('#joueursAdv' + numeroEquipe).html(response);
}

let alreadyCalledComposAdversaires = [];