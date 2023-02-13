function toggleCompoValidation(idRencontre) {
    sendingCompoValidation(idRencontre);
    $.ajax({
        url : '/backoffice/rencontre/update/validation/' + idRencontre,
        type : 'POST',
        dataType : 'json',
        success : function(response) { endDendingCompoValidation(response, idRencontre, true); },
        error : function(error) { endDendingCompoValidation(error, idRencontre, false); }
    });
}

function sendingCompoValidation(divSuffixe){
    $('#preloader' + divSuffixe).show();
    $('#compoValidation' + divSuffixe).hide();
}

function endDendingCompoValidation(response, divSuffixe, isSuccess) {
    $('#compoValidation' + divSuffixe).show();
    if (!isSuccess || !response.status) M.toast({html: response.message});
    else {
        $('#pastilleBorder' + divSuffixe).toggleClass('enAttente').toggleClass('validee')
        $('#pastilleBorderContentText' + divSuffixe).html(response.isValide ? `<i class="material-icons">checklist_rtl</i> Équipe confirmée` : `<i class="material-icons">rule</i> Attente de confirmation`)
    }
    $('#preloader' + divSuffixe).hide();
}