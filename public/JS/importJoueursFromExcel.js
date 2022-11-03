function importJoueursFromExcel(file) {
    $.ajax({
        url : '/backoffice/competiteurs/import-file/read',
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
    $('a#retourMembres').addClass('hide');
    $('div#tableJoueursImportesLoader').removeClass('hide');
    $('div#tableJoueursImportes').addClass('hide');
    $('div#btnFileInputExcelDocument').addClass('disabled');
    $('input#filePathExcelDocument').prop('disabled', true);
    $('input#excelDocument').prop('disabled', true);
}

function endSending(responseTemplate){
    $('div#tableJoueursImportesLoader').addClass('hide');
    $('div#tableJoueursImportes').removeClass('hide');
    $('#tableJoueursImportes').html(responseTemplate);
    $('div#btnFileInputExcelDocument').removeClass('disabled');
    $('input#filePathExcelDocument').prop('disabled', false);
    $('input#excelDocument').prop('disabled', false);
}

$(document).ready(() => {
    let inputFile = $('input#excelDocument');

    // Permet d'importer le même fichier après correction
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

function getUsernamesToRegister() {
    let checkboxJoueurs = Array.from($("input:checkbox:checked"))
    $("input:hidden[name='usernamesToRegister']")[0].value = JSON.stringify(checkboxJoueurs.map(el => {
        return el.value;
    }));
}