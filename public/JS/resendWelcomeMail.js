function renvoyerMailBienvenue(idCompetiteur, prenom) {
    let r = confirm('Renvoyer le mail de bienvenue à ' + prenom + ' ?')
    if (r) {
        sending();
        $.ajax({
            url : '/backoffice/competiteur/resend-welcome-mail',
            type : 'POST',
            data: {
                idCompetiteur: idCompetiteur
            },
            dataType : 'json',
            success : function(response) { endSending(response.message, response.success); },
            error : function() { endSending('Une erreur est survenue !', false); }
        });
    }
}

function sending(){
    $('i#iconRenvoiMailBienvenue').prop('disabled', true).html('sync').addClass('rotating-icon');
}

function endSending(message, success){
    $('i#iconRenvoiMailBienvenue').prop('disabled', true).html('outgoing_mail').removeClass('rotating-icon');
    M.toast({html: message});
}