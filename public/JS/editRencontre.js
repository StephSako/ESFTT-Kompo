$(document).ready(function () {
    let lieu_rencontre = $('#lieu_rencontre');
    let exempt = $('#exempt');
    let adversaire = $('#adversaire');
    let ville_host = $('#ville_host');
    let reported = $('#reporte');

    if (!reported.is(':checked')) $('#rencontre_dateReport .select-dropdown').prop('disabled', true);

    if (exempt.is(':checked')) {
        reported.prop('disabled', true);
        adversaire.val("").attr('placeholder', "Pas d'adversaire");
        adversaire.prop('disabled', true);
        lieu_rencontre.prop('disabled', true);
        ville_host.val("").attr('placeholder', "Pas de délocalisation");
        ville_host.prop('disabled', true);
    }

    exempt.change(function () {
        if (this.checked) {
            reported.prop('checked', false).prop('disabled', true);
            lieu_rencontre.val('0');
            lieu_rencontre.formSelect();
            adversaire.val("").attr('placeholder', "Pas d'adversaire");
            adversaire.prop('disabled', true);
            ville_host.val("").attr('placeholder', "Pas de délocalisation");
            ville_host.prop('disabled', true);
            reported.prop('disabled', true);
            $('.select-dropdown').prop('disabled', true);
        } else {
            adversaire.attr('placeholder', "Adversaire");
            adversaire.prop('disabled', false);
            ville_host.attr('placeholder', "Pas de délocalisation");
            ville_host.prop('disabled', false);
            reported.prop('disabled', false);
            lieu_rencontre.val(lieu_rencontre_value);
            lieu_rencontre.formSelect();
            $('.select-dropdown').prop('disabled', true);
            $('.lieu_rencontre .select-dropdown').prop('disabled', false);
        }
    });

    reported.change(function () {
        $('#rencontre_dateReport .select-dropdown').prop('disabled', !this.checked);
    });
});