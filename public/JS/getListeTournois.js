function getListeTournois() {
    $.ajax({
        url: '/tournois/liste',
        type: 'GET',
        dataType: 'json',
        success: (responseTemplate) => {
            templatingListeTournois('#rankingContent', responseTemplate, true);
        },
        error: () => {
            templatingListeTournois('#rankingContent', "<p style='margin: 8px auto' class='pastille reset red'>Le service de l'Espace MonClub rencontre des perturbations. Réessayez plus tard</p>", false);
        }
    });
}

function templatingListeTournois(selector, response, isSuccess) {
    $('#listTournois').removeClass('center').html(response);
    $('.collapsible').collapsible({
        accordion: false
    });

    if (isSuccess) {
        // Quand l'ID d'un tournoi est partagé dans l'URL
        let hash = window.location.hash;
        let elem = $('li' + hash);
        if (hash.length > 0 && elem.length > 0) {
            hash = hash.replace('#', '');
            elem.removeClass('hide');
            elem.addClass('active');
            $('#c-b' + hash).css('display', 'block');
            jumpTo(hash);
            getDetailsTableauxTournoi(hash);
        } else if (hash.length > 0 && elem.length === 0) {
            $('#expiredLink').removeAttr('hidden');
        }
    }
}

function switchUnjoinableTournois() {
    displayUnjoinableTournois = !displayUnjoinableTournois;

    if (displayUnjoinableTournois) {
        $('li.notJoinable').removeClass('hide');
        $('p.moisToHide').removeClass('hide');
        $('p.notJoinable.message').removeClass('hide');
    } else {
        $('li.notJoinable').addClass('hide');
        $('p.moisToHide').addClass('hide');
        $('p.notJoinable.message').addClass('hide');
    }
}

let displayUnjoinableTournois = false;