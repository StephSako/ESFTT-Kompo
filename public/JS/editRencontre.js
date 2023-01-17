$(document).ready(() => {
    let lieu_rencontre = $('.lieu_rencontre .select-wrapper input.select-dropdown');
    let exempt = $('#exempt');
    let adversaire = $('#adversaire');
    let ville_host = $('#ville_host');
    let reported = $('#reporte');
    let selects = $('#rencontre_dateReport .select-dropdown');

    if (!reported.is(':checked')) selects.prop('disabled', true);

    if (exempt.is(':checked')) {
        reported.prop('disabled', true);
        adversaire.val("").attr('placeholder', "Pas d'adversaire").prop('disabled', true);
        lieu_rencontre.prop('disabled', true);
        ville_host.prop('disabled', true);
    }

    exempt.change((e) => {
        reported.prop('disabled', e.currentTarget.checked);
        adversaire.prop('disabled', e.currentTarget.checked);
        ville_host.prop('disabled', e.currentTarget.checked);
        lieu_rencontre.prop('disabled', e.currentTarget.checked);
        selects.prop('disabled', true);
        if (e.currentTarget.checked) {
            reported.prop('checked', false);
            adversaire.val("").attr('placeholder', "Pas d'adversaire");
            ville_host.val("");
        } else {
            adversaire.attr('placeholder', "Adversaire");
        }
    });

    reported.change((e) => {
        $('#rencontre_dateReport .select-dropdown').prop('disabled', !e.currentTarget.checked);
    });
});