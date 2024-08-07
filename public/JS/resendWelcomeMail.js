function renvoyerMailBienvenue(idCompetiteur, prenom) {
    let r = confirm('Renvoyer l\'e-mail de bienvenue à ' + prenom + ' ? Le lien du mot de passe sera inutilisable si le mot de passe a déjà été initialisé.')
    if (r) {
        sendingRenvoyerMailBienvenue();
        $.ajax({
            url: '/backoffice/competiteur/resend-welcome-mail',
            type: 'POST',
            data: {
                idCompetiteur: idCompetiteur
            },
            dataType: 'json',
            success: (response) => {
                endSendingRenvoyerMailBienvenue(response.message);
            },
            error: () => {
                endSendingRenvoyerMailBienvenue('Une erreur est survenue !');
            }
        });
    }
}

function sendingRenvoyerMailBienvenue() {
    $('button#btnRenvoiMailBienvenue').prop('disabled', true)
    $('i#iconRenvoiMailBienvenue').html('sync').addClass('rotating-icon');
}

function endSendingRenvoyerMailBienvenue(message) {
    $('button#btnRenvoiMailBienvenue').prop('disabled', false)
    $('i#iconRenvoiMailBienvenue').html('outgoing_mail').removeClass('rotating-icon');
    M.toast({html: message});
}