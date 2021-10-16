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
    let r;
    if (disponibiliteBoolean === 0) r = confirm('Le joueur pourrait être désélectionné pour cette journée. Êtes-vous sûr ?');
    if ((disponibiliteBoolean === 0 && r) || disponibiliteBoolean === 1){
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
}

function sending(divSuffixe){
    $('#preloader' + divSuffixe).show();
    $('#dispoJoueur' + divSuffixe).hide();
}

function endSending(response, divSuffixe, isSuccess){
    if (!isSuccess) M.toast({html: response.responseJSON});
    else {
        let buttonDiv = $('#dispoJoueur' + divSuffixe);
        buttonDiv.show();
        buttonDiv.html(response);
    }
    $('#preloader' + divSuffixe).hide();
}