$(document).ready(function () {
    let lieu_rencontre = $('#lieu_rencontre');
    let span_domicile = $('#domicile');
    let span_exterieur = $('#exterieur');
    let exempt = $('#exempt');
    let adversaire = $('#adversaire');
    let hosted = $('#hosted');
    let ville_host = $('#ville_host');
    let reported = $('#reporte');

    if (exterieur === "D") {
        span_domicile.css("font-weight", "bold");
        span_exterieur.css("font-weight", "normal");
    } else if (exterieur === "E") {
        lieu_rencontre.prop('checked', true);
        span_domicile.css("font-weight", "normal");
        span_exterieur.css("font-weight", "bold");
    }

    if (!reported.is(':checked')) $('.select-dropdown').prop('disabled', true);

    if (!hosted.is(':checked')){
        ville_host.val("").attr('placeholder', "Pas de ville de remplacement");
        ville_host.prop('disabled', true);
    }

    if (exempt.is(':checked')) {
        reported.prop('disabled', true);
        adversaire.val("").attr('placeholder', "Pas d'adversaire");
        adversaire.prop('disabled', true);
        lieu_rencontre.prop('disabled', true);
        hosted.prop('disabled', true);
        ville_host.val("").attr('placeholder', "Pas de ville de remplacement");
        ville_host.prop('disabled', true);
        span_domicile.css("font-weight", "normal");
        span_exterieur.css("font-weight", "normal");
        span_domicile.css("color", "#a4a2a2");
        span_exterieur.css("color", "#a4a2a2");
    }

    exempt.change(function () {
        $('.select-dropdown').prop('disabled', true);
        if (this.checked) {
            reported.prop('checked', false).prop('disabled', true);
            lieu_rencontre.prop('checked', false).prop('disabled', true);
            hosted.prop('checked', false).prop('disabled', true);
            adversaire.val("").attr('placeholder', "Pas d'adversaire");
            adversaire.prop('disabled', true);
            ville_host.val("").attr('placeholder', "Pas de ville de remplacement");
            ville_host.prop('disabled', true);
            span_domicile.css("font-weight", "normal");
            span_exterieur.css("font-weight", "normal");
            span_domicile.css("color", "#a4a2a2");
            span_exterieur.css("color", "#a4a2a2");
            reported.prop('disabled', true);
        } else {
            hosted.prop('checked', false).prop('disabled', false);
            adversaire.attr('placeholder', "Adversaire");
            adversaire.prop('disabled', false);
            ville_host.attr('placeholder', "Pas de ville de remplacement");
            ville_host.prop('disabled', true);
            lieu_rencontre.prop('disabled', false);
            span_domicile.css("font-weight", "bold");
            span_domicile.css("color", "#000000");
            span_exterieur.css("color", "#000000");
            reported.prop('disabled', false);
        }
    });

    reported.change(function () {
        if (this.checked) $('.select-dropdown').prop('disabled', false);
        else $('.select-dropdown').prop('disabled', true);
    });

    lieu_rencontre.change(function () {
        if (this.checked) {
            span_domicile.css("font-weight", "normal");
            span_exterieur.css("font-weight", "bold");
        } else {
            span_domicile.css("font-weight", "bold");
            span_exterieur.css("font-weight", "normal");
        }
    });

    hosted.change(function () {
        if (this.checked) {
            ville_host.attr('placeholder', "Ville de remplacement");
            ville_host.prop('disabled', false);
        } else {
            ville_host.val("").attr('placeholder', "Pas de ville de remplacement");
            ville_host.prop('disabled', true);
        }
    });
});