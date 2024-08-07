function getDetailsTableauxTournoi(idTournoi) {
    if (!alreadyCalledTournois.includes(idTournoi)) {
        alreadyCalledTournois.push(idTournoi);
        $.ajax({
            url: '/tournois/liste/tableaux',
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

function showIframe(idMap) {
    let iframe = $('iframe#tournoi-' + idMap)[0];
    iframe.width = "65%";
    iframe.height = "350px";
    iframe.style.opacity = "1";
    $('#preloader-tournoi-' + idMap).attr("hidden", "")
}

function hideIframe(idMap) {
    $('iframe#div-tournoi-' + idMap)[0].attr("hidden", "");
}

let alreadyCalledTournois = [];