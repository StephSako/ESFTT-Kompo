function newDisponibilite(idJournee, disponibiliteBoolean, idCompetiteur) {
    sendingDisponibilite(idJournee + idCompetiteur);
    $.ajax({
        url : '/backoffice/disponibilites/new',
        type : 'POST',
        data: {
            idJournee: idJournee,
            disponibiliteBoolean: disponibiliteBoolean,
            idCompetiteur: idCompetiteur
        },
        dataType : 'json',
        success : function(response) { endSendingDisponibilite(response, idJournee + idCompetiteur, true); },
        error : function(error) { endSendingDisponibilite(error, idJournee + idCompetiteur, false); }
    });
}

function updateDisponibilite(idCompetiteur, idDisponibilite, disponibiliteBoolean, idJournee) {
    let r;
    if (disponibiliteBoolean === 0) r = confirm('Le joueur pourrait être désélectionné pour cette journée. Êtes-vous sûr ?');
    if ((disponibiliteBoolean === 0 && r) || disponibiliteBoolean === 1){
        sendingDisponibilite(idJournee + idCompetiteur);
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
            success : function(response) { endSendingDisponibilite(response, idJournee + idCompetiteur, true); },
            error : function(error) { endSendingDisponibilite(error, idJournee + idCompetiteur, false); }
        });
    }
}

function deleteDisponibilite(idCompetiteur, idDisponibilite, disponibiliteBoolean, idJournee) {
    let r;
    if (disponibiliteBoolean === 1) r = confirm('Le joueur pourrait être désélectionné pour cette journée. Êtes-vous sûr ?');
    if ((disponibiliteBoolean === 1 && r) || disponibiliteBoolean === 0) {
        sendingDisponibilite(idJournee + idCompetiteur);
        $.ajax({
            url: '/backoffice/disponibilites/delete',
            type: 'POST',
            data: {
                idDisponibilite: idDisponibilite,
                idCompetiteur: idCompetiteur,
                idJournee: idJournee
            },
            dataType: 'json',
            success: function (response) {
                endSendingDisponibilite(response, idJournee + idCompetiteur, true);
            },
            error: function (error) {
                endSendingDisponibilite(error, idJournee + idCompetiteur, false);
            }
        });
    }
}

function sendingDisponibilite(divSuffixe){
    $('#preloader' + divSuffixe).show();
    $('#dispoJoueur' + divSuffixe).hide();
}

function endSendingDisponibilite(response, divSuffixe, isSuccess){
    let buttonDiv = $('#dispoJoueur' + divSuffixe);
    buttonDiv.show();
    if (!isSuccess) M.toast({html: response.responseJSON});
    else buttonDiv.html(response);
    $('#preloader' + divSuffixe).hide();
}