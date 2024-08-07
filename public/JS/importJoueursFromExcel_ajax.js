$(document).ready(() => {
    $('.tooltipped').tooltip();
})

function changeColor(id) {
    let input = $('input#' + id)
    let tr = $('tr#tr_' + id)
    let pastilles = $('tr#tr_' + id + '>td span.pastille')
    let badges = $('tr#tr_' + id + '>td span.new.badge')
    let icons = $('tr#tr_' + id + '>td i.material-icons')

    if (input.is(":checked")) {
        tr.removeClass('grey-text lighten-1')
        badges.removeClass('lighten-3')
        pastilles.removeClass('lighten-3')
        icons.removeClass('text-lighten-3')
        tr.addClass('black-text')
    } else {
        tr.addClass('grey-text lighten-1')
        badges.addClass('lighten-3')
        pastilles.addClass('lighten-3')
        icons.addClass('text-lighten-3')
        tr.removeClass('black-text')
    }

    // On bloque/débloque le bouton 'Envoyer' si aucun joueur n'est sélectionné
    if (!getCheckedJoueurs().length) {
        $('#submitImportButton').addClass('hide')
        $('#noPeopleToImport').removeClass('hide')
        $('p#nbFutursMembres').addClass('hide');
    } else {
        $('#submitImportButton').removeClass('hide')
        $('#noPeopleToImport').addClass('hide')
        $('p#nbFutursMembres').removeClass('hide')
    }

    setNbFutursMembres();
}

function setNbFutursMembres() {
    let textNbFutursMembres = null;
    if (getCheckedJoueurs().length > 1) textNbFutursMembres = getCheckedJoueurs().length + ' membres seront inscrits';
    else if (getCheckedJoueurs().length === 1) textNbFutursMembres = getCheckedJoueurs().length + ' membre sera inscris';
    $('p#nbFutursMembres span').text(' ' + textNbFutursMembres)
}

function getCheckedJoueurs() {
    let checkboxJoueurs = Array.from($("input:checkbox:checked"))
    return checkboxJoueurs.map(el => {
        return el.value;
    })
}

function getUsernamesToRegister() {
    $('#progress-bar').removeClass('hide');
    $('#importButtons').addClass('hide');
    $('button#enregistrerJoueurs').addClass('disabled');
    $('a#annulerImportJoueurs').addClass('disabled');
    $("input:hidden[name='usernamesToRegister']")[0].value = JSON.stringify(getCheckedJoueurs());
}