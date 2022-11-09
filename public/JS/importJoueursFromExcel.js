function importJoueursFromExcel(file) {
    $.ajax({
        url : '/backoffice/competiteurs/import-file/read',
        type : 'POST',
        data: file,
        processData: false,
        contentType: false,
        cache: false,
        dataType:'json',
        success : function(responseTemplate) { endSending(responseTemplate, false); },
        error : function() { endSending("<span class='pastille reset red lighten-1 white-text'>Le document importé n\'est pas valide !</span>", true); }
    });
}

function sending(){
    $('a#retourMembres').addClass('hide');
    $('div#tableJoueursImportesLoader').removeClass('hide');
    $('div#tableJoueursImportes').addClass('hide');
    $('div#btnFileInputExcelDocument').addClass('disabled');
    $('input#filePathExcelDocument').prop('disabled', true);
    $('input#excelDocument').prop('disabled', true);
}

function endSending(responseTemplate, isError){
    if (isError) $('a#retourMembres').removeClass('hide');
    $('div#tableJoueursImportesLoader').addClass('hide');
    $('div#tableJoueursImportes').removeClass('hide');
    $('div#tableJoueursImportes').html(responseTemplate);
    $('div#btnFileInputExcelDocument').removeClass('disabled');
    $('input#filePathExcelDocument').prop('disabled', false);
    $('input#excelDocument').prop('disabled', false);
}

$(document).ready(() => {
    // Permet d'importer le même fichier après correction
    let inputFile = $('input#excelDocument');
    inputFile.click((e) => {
        e.target.value = null;
    });

    inputFile.change((e) => {
        sending();
        let file = e.target.files[0];
        let formData = new FormData();
        formData.append('excelDocument', file);
        importJoueursFromExcel(formData)
    });
})