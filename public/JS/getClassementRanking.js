function getClassementPoule(lienDivision, numeroEquipe) {
    if (!alreadyCalledClassementPoule.includes(numeroEquipe)) {
        alreadyCalledClassementPoule.push(numeroEquipe);
        $.ajax({
            url : '/journee/classement-poule',
            type : 'POST',
            data: {
                lienDivision: lienDivision /** Propriété d'Equipe pour récupérer le classement de la poule */
            },
            dataType : 'json',
            success : function(responseTemplate) { templatingClassementPoule(numeroEquipe, responseTemplate); },
            error : function() { templatingClassementPoule(numeroEquipe, "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>"); }
        });
    }
}

function templatingClassementPoule(numeroEquipe, response){
    $('#classementContent' + numeroEquipe).html(response);
}

let alreadyCalledClassementPoule = [];