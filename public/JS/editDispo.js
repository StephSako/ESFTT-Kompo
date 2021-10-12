function editDispo(idJournee, dispo, idCompetiteur) {
    sending();
    $.ajax({
        url : '/disponibilites/new',
        type : 'POST',
        data: {
            journee: idJournee,
            dispo: dispo,
            idCompetiteur: idCompetiteur
        },
        dataType : 'json',
        success : function(response) { endSending(response, idJournee, idCompetiteur); },
        error : function(error) { endSending(error, null, null); }
    });
}

function sending(){
    $("#preloaderResetPassword").show();
    $('#buttonsResetPassword').hide();
    $('#email').prop('disabled', true);
    $('#username').prop('disabled', true);
}

function endSending(response, idJournee, idCompetiteur){
    console.log(response);
    /*$('#joueursAdv' + idJournee + idCompetiteur).html(response);
    $("#preloaderResetPassword").hide();
    $('#buttonsResetPassword').show();
    $('#email').prop('disabled', false);
    $('#username').prop('disabled', false);*/
}