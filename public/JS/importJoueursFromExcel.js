function importJoueursFromExcel(file) {
    $.ajax({
        url: '/backoffice/competiteur/import-file/read',
        type: 'POST',
        data: file,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        success: function (responseTemplate) {
            endSendingImportJoueursFromExcel(responseTemplate, false);
        },
        error: function () {
            endSendingImportJoueursFromExcel("<span class='pastille reset red lighten-1 white-text'>Le document importé n\'est pas valide, utilisez le template fourni dans Instructions !</span>", true);
        }
    });
}

function sendingImportJoueursFromExcel() {
    $('a#retourMembres').addClass('hide');
    $('#tableJoueursImportesLoader').removeClass('hide');
    $('#tableJoueursImportes').addClass('hide');
    $('#btnFileInputExcelDocument').addClass('disabled');
    $('input#filePathExcelDocument').prop('disabled', true);
    $('input#excelDocument').prop('disabled', true);
}

function endSendingImportJoueursFromExcel(responseTemplate, isError) {
    if (isError) $('a#retourMembres').removeClass('hide');
    $('#tableJoueursImportesLoader').addClass('hide');
    $('#tableJoueursImportes').removeClass('hide');
    $('#tableJoueursImportes').html(responseTemplate);
    $('#btnFileInputExcelDocument').removeClass('disabled');
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
        sendingImportJoueursFromExcel();
        let file = e.target.files[0];
        let formData = new FormData();
        formData.append('excelDocument', file);
        importJoueursFromExcel(formData)
    });
})