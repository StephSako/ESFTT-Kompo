function importJoueursFromExcel(file) {
    sending();
    $.ajax({
        url : '/backoffice/competiteurs/import-file',
        type : 'POST',
        data: file,
        processData: false,
        contentType: false,
        cache: false,
        dataType:'json',
        success : function(responseTemplate) { endSending(responseTemplate); },
        error : function() { endSending('Une erreur est survenue !'); }
    });
}

function sending(){
    $('div#tableJoueursImportesLoader').removeClass('hide');
    $('div#tableJoueursImportes').addClass('hide');
    // TODO Disabled l'input

    // $('button#btnRenvoiMailBienvenue').prop('disabled', true)
    // $('i#iconRenvoiMailBienvenue').html('sync').addClass('rotating-icon');
}

function endSending(responseTemplate){
    $('div#tableJoueursImportesLoader').addClass('hide');
    $('div#tableJoueursImportes').removeClass('hide');
    $('#tableJoueursImportes').html(responseTemplate);
    // TODO Enabled l'input
    // Récupérer le tableau de joueurs

    // $('button#btnRenvoiMailBienvenue').prop('disabled', false)
    // $('i#iconRenvoiMailBienvenue').html('outgoing_mail').removeClass('rotating-icon');
    // M.toast({html: message});
}

$(document).ready(function() {
    let inputFile = $('input#excel_file');

    inputFile.click((e) => {
        e.target.value = null;
    });

    inputFile.change((e) => {
        let file = e.target.files[0];
        let formData = new FormData();
        formData.append('excelDocument', file);
        importJoueursFromExcel(formData)
    });
})