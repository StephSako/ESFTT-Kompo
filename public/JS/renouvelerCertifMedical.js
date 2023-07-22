function renouvelerCertifMedical(idCompetiteur, prenom, isFromAdmin) {
    let r = confirm('Renouveler le certificat médical de ' + prenom + ' ?')
    if (r) {
        sendingRenouvelerCertifMedical(idCompetiteur);
        $.ajax({
            url: '/backoffice/competiteur/renouveler/certificat/' + idCompetiteur,
            type: 'POST',
            dataType: 'json',
            success: (response) => {
                endSendingRenouvelerCertifMedical(response, idCompetiteur, isFromAdmin, prenom)
            },
            error: () => {
                endSendingRenouvelerCertifMedical({status: false, message: 'Une erreur est survenue'}, idCompetiteur, isFromAdmin, prenom)
            }
        });
    }
}

function sendingRenouvelerCertifMedical(idCompetiteur) {
    $('#loaderCertif' + idCompetiteur).show()
    $('#btnCertif' + idCompetiteur).hide()
}

function endSendingRenouvelerCertifMedical(response, idCompetiteur, isFromAdmin, prenom) {
    if (!response.status) {
        M.toast({html: response.message})
        $('#btnCertif' + idCompetiteur).show()
    } else {
        M.toast({html: 'Certificat médical de ' + prenom + ' renouvelé'})
        let labelRenewCertif = $('.label-renew-certif' + idCompetiteur)
        if (isFromAdmin) $('#icon-renew-certif' + idCompetiteur).removeClass('red-text').addClass('green-text')
        else labelRenewCertif.removeClass('red').addClass('green')
        labelRenewCertif.html(!isFromAdmin ? 'Renouvellement pour la rentrée<br><b>' + response.message + '</b>' : response.message)

        // Update du nombre de certificats médicaux invalides sur le bouton
        let btnAlert = $('#badgeCertifInvalidButton')
        let val = parseInt(btnAlert.text())
        val--
        btnAlert.text(val)

        // Update de la table dans la modale des joueurs ayant un certificat médical invalide
        $('#contactable_' + idCompetiteur).remove()
        $('#notContactables_' + idCompetiteur).remove()
    }
    $('#loaderCertif' + idCompetiteur).hide()
}