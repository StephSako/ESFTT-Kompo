$(document).ready(function () {
    if (exterieur === "D") {
        span_domicile.css("font-weight", "bold");
        span_exterieur.css("font-weight", "normal");
    } else if (exterieur === "E") {
        lieu_rencontre.prop('checked', true);
        hosted.prop('disabled', true);
        span_domicile.css("font-weight", "normal");
        span_exterieur.css("font-weight", "bold");
    }

    if (reported.is(':checked') === false) $('.select-dropdown').prop('disabled', true);

    if (exempt.is(':checked')) {
        reported.prop('disabled', true);
        adversaire.val("").attr('placeholder', "Pas d'adversaire");
        adversaire.prop('disabled', true);
        lieu_rencontre.prop('checked', false).prop('disabled', true);
        hosted.prop('checked', false).prop('disabled', true);
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
            span_domicile.css("font-weight", "normal");
            span_exterieur.css("font-weight", "normal");
            span_domicile.css("color", "#a4a2a2");
            span_exterieur.css("color", "#a4a2a2");
            reported.prop('disabled', true);
        } else {
            hosted.prop('checked', false).prop('disabled', false);
            adversaire.attr('placeholder', "Adversaire");
            adversaire.prop('disabled', false);
            lieu_rencontre.prop('disabled', false);
            span_domicile.css("font-weight", "bold");
            span_domicile.css("color", "#000000");
            span_exterieur.css("color", "#000000");
            reported.prop('disabled', false);
        }
    });

    reported.change(function () {
        if (this.checked) {
            exempt.prop('disabled', true);
            $('.select-dropdown').prop('disabled', false);
        } else {
            exempt.prop('disabled', false);
            $('.select-dropdown').prop('disabled', true);
        }
    });

    lieu_rencontre.change(function () {
        if (this.checked) {
            hosted.prop('checked', false).prop('disabled', true);
            span_domicile.css("font-weight", "normal");
            span_exterieur.css("font-weight", "bold");
        } else {
            hosted.prop('disabled', false);
            span_domicile.css("font-weight", "bold");
            span_exterieur.css("font-weight", "normal");
        }
    });
});