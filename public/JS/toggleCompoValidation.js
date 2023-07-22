function toggleCompoValidation(idRencontre) {
    sendingCompoValidation(idRencontre);
    $.ajax({
        url: '/backoffice/rencontre/update/validation/' + idRencontre,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            endSendingCompoValidation(response, idRencontre, true);
        },
        error: function (error) {
            endSendingCompoValidation(error, idRencontre, false);
        }
    });
}

function sendingCompoValidation(divSuffixe) {
    $('#preloader' + divSuffixe).show();
    $('#compoValidation' + divSuffixe).hide();
}

function endSendingCompoValidation(response, divSuffixe, isSuccess) {
    $('#compoValidation' + divSuffixe).show();
    if (!isSuccess || !response.status) {
        M.toast({html: response.message})
        $('#compoEdition' + divSuffixe).removeClass('hide');
    } else {
        if (!response.isValide) $('#compoEdition' + divSuffixe).removeClass('hide');
        else $('#compoEdition' + divSuffixe).addClass('hide');

        $('#pastilleBorder' + divSuffixe).toggleClass('enAttente').toggleClass('validee')
        $('#pastilleBorderContentText' + divSuffixe).html(response.isValide ? `<i class="material-icons">checklist_rtl</i> Équipe confirmée` : `<i class="material-icons">rule</i> Équipe non confirmée`)
    }
    $('#preloader' + divSuffixe).hide();
}