function uploadFile(file, idSetting) {
    $.ajax({
        url: '/informations/' + idSetting + '/upload-file',
        type: 'POST',
        data: file,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        success: function (r) {
            endUploadingFile(r.message, r.success);
        },
        error: function (e) {
            endSendingImportJoueursFromExcel(e.message, false);
        }
    });
}

function uploadingFile() {
    $('form[name="settings"] button').prop('disabled', true);
    $('form[name="settings"] input').prop('disabled', true);
}

function endUploadingFile(message, isSuccess) {
    console.error(message)
    console.error(isSuccess)
    if (!isSuccess) {
        console.error($('p#erreur-upload'))
        $('p#erreur-upload').removeClass('hide');
        $('p#erreur-upload').html(message);
    }
    $('form[name="settings"] button').removeAttr('disabled');
    $('form[name="settings"] input').removeAttr('disabled');
}

$(document).ready(() => {
    // Permet d'importer le même fichier après correction
    let inputFile = $('input#uploadFile');
    inputFile.click((e) => {
        e.target.value = null;
    });

    inputFile.change((e) => {
        $('p#erreur-upload').addClass('hide');
        uploadingFile();
        let file = e.target.files[0];
        let formData = new FormData();
        formData.append('uploadFile', file);
        uploadFile(formData, idSetting)
    });
})