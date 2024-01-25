function uploadFile(file, idSetting) {
    console.log(idSetting)
    $.ajax({
        url: '/informations/' + idSetting + '/upload-file',
        type: 'POST',
        data: file,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        success: function (responseTemplate) {
            // endSendingImportJoueursFromExcel(responseTemplate, false);
        },
        error: function () {
            // endSendingImportJoueursFromExcel("<span class='pastille reset red lighten-1 white-text'>Le document importé n\'est pas valide, utilisez le template fourni dans Instructions !</span>", true);
        }
    });
}

//
// function sendingImportJoueursFromExcel() {
//     $('a#retourMembres').addClass('hide');
//     $('#tableJoueursImportesLoader').removeClass('hide');
//     $('#tableJoueursImportes').addClass('hide');
//     $('#btnFileInputuploadFile').addClass('disabled');
//     $('input#filePathuploadFile').prop('disabled', true);
//     $('input#uploadFile').prop('disabled', true);
// }
//
// function endSendingImportJoueursFromExcel(responseTemplate, isError) {
//     if (isError) $('a#retourMembres').removeClass('hide');
//     $('#tableJoueursImportesLoader').addClass('hide');
//     $('#tableJoueursImportes').removeClass('hide');
//     $('#tableJoueursImportes').html(responseTemplate);
//     $('#btnFileInputuploadFile').removeClass('disabled');
//     $('input#filePathuploadFile').prop('disabled', false);
//     $('input#uploadFile').prop('disabled', false);
// }

$(document).ready(() => {
    console.log(idSetting)
    // Permet d'importer le même fichier après correction
    let inputFile = $('input#uploadFile');
    inputFile.click((e) => {
        e.target.value = null;
    });

    inputFile.change((e) => {
        // sendingImportJoueursFromExcel();
        let file = e.target.files[0];
        let formData = new FormData();
        formData.append('uploadFile', file);
        uploadFile(formData, idSetting)
    });
})