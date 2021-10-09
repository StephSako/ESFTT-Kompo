function getLastComposAdversaire(nomAdversaire, lienDivision, numeroEquipe) {
    sending();
    $.ajax({
        url : '/journee/last_compos_adversaire',
        type : 'POST',
        data: {
            nomAdversaire: nomAdversaire,
            lienDivision: lienDivision // Propriété d'Equipe pour récupérer le classement de la poule pour avoir les numéros des clubs
        },
        dataType : 'json',
        success : function(response) { endSending(response, numeroEquipe); },
        error : function(error) { endSending(error, numeroEquipe); }
    });
}

function sending(){
    $("#preloaderResetPassword").show();
    $('#buttonsResetPassword').hide();
    $('#email').prop('disabled', true);
    $('#username').prop('disabled', true);
}

function endSending(response, numeroEquipe){
    $('#joueursAdv' + numeroEquipe).html(response);
    $("#preloaderResetPassword").hide();
    $('#buttonsResetPassword').show();
    $('#email').prop('disabled', false);
    $('#username').prop('disabled', false);
}