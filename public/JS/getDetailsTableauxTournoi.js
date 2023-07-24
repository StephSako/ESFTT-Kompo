function getDetailsTableauxTournoi(idTournoi) {
    if (!alreadyCalledTournois.includes(idTournoi)) {
        alreadyCalledTournois.push(idTournoi);
        $.ajax({
            url: '/liste/tableaux/tournois',
            type: 'POST',
            data: {
                id: idTournoi
            },
            dataType: 'json',
            success: (responseTemplate) => {
                templatingDetailsTableauxTournoi(idTournoi, responseTemplate);
            },
            error: () => {
                templatingDetailsTableauxTournoi(idTournoi, "<p style='margin: 8px auto' class='pastille reset red'>Le service de l'Espace MonClub rencontre des perturbations. Réessayez plus tard</p>");
            }
        });
    }
}

function templatingDetailsTableauxTournoi(idTournoi, tableaux) {
    $('#preloaderTableauxTournoi' + idTournoi).removeClass('center').html(tableaux);
    resizeTable();
}

let alreadyCalledTournois = [];