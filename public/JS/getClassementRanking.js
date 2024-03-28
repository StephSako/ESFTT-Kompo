function getClassementPoule(lienDivision, numeroEquipe, division, poule, forceReload) {
    if (!alreadyCalledClassementPoule.includes(numeroEquipe) || forceReload) {
        alreadyCalledClassementPoule.push(numeroEquipe);
        if (forceReload) {
            $('a.reload_classement_poule').addClass('hide');
            $('#classementRencontresPoulesLoader' + numeroEquipe).removeClass('hide');
            $('#classementRencontresPoulesContent' + numeroEquipe).addClass('hide');
        }
        $.ajax({
            url: '/journee/classement-poule',
            type: 'POST',
            data: {
                lienDivision: lienDivision, /** Propriété d'Equipe pour récupérer le classement de la poule */
                division: division,
                poule: poule
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingClassementPoule(numeroEquipe, responseTemplate);
            },
            error: () => {
                templatingClassementPoule(numeroEquipe, "<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>");
            }
        });
    }
}

function templatingClassementPoule(numeroEquipe, response) {
    $('a.reload_classement_poule').removeClass('hide');
    $('#classementRencontresPoulesLoader' + numeroEquipe).addClass('hide');
    $('#classementRencontresPoulesContent' + numeroEquipe).html(response).removeClass('hide');
}

let alreadyCalledClassementPoule = [];