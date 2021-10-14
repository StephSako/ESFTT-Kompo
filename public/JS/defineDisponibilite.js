function newDisponibilite(idJournee, disponibiliteBoolean, idCompetiteur) {
    sending(idJournee + idCompetiteur);
    $.ajax({
        url : '/backoffice/disponibilites/new',
        type : 'POST',
        data: {
            idJournee: idJournee,
            disponibiliteBoolean: disponibiliteBoolean,
            idCompetiteur: idCompetiteur
        },
        dataType : 'json',
        success : function(response) { endSending(response, idJournee + idCompetiteur, true); },
        error : function(error) { endSending(error, null, false); }
    });
}

function updateDisponibilite(idCompetiteur, idDisponibilite, disponibiliteBoolean, idJournee) {
    sending(idJournee + idCompetiteur);
    $.ajax({
        url : '/backoffice/disponibilites/update',
        type : 'POST',
        data: {
            idDisponibilite: idDisponibilite,
            disponibiliteBoolean: disponibiliteBoolean,
            idCompetiteur: idCompetiteur,
            idJournee: idJournee
        },
        dataType : 'json',
        success : function(response) { endSending(response, idJournee + idCompetiteur, true); },
        error : function(error) { endSending(error, null, false); }
    });
}

function sending(divSuffixe){ //TODO Afficher le preloader
    //$("#preloaderResetPassword").show();
    $('#dispoJoueur' + divSuffixe).hide();
}

function endSending(response, divSuffixe, isSuccess){ //TODO Cacher le preloader
    if (!isSuccess) M.toast({html: response.responseJSON});
    let buttonDiv = $('#dispoJoueur' + divSuffixe);
    buttonDiv.show();
    buttonDiv.html(response);

    /*$("#preloaderResetPassword").hide();
    $('#buttonsResetPassword').show();
    $('#email').prop('disabled', false);
    $('#username').prop('disabled', false);*/
}