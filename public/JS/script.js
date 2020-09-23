$(document).ready(function() {
    let exemptDep = $('#back_office_rencontre_departementale_exempt');
    let adversaireDep = $('#back_office_rencontre_departementale_adversaire');
    let hostedDep = $('#back_office_rencontre_departementale_hosted');

    let exemptPar = $('#back_office_rencontre_paris_exempt');
    let adversairePar = $('#back_office_rencontre_paris_adversaire');
    let hostedPar = $('#back_office_rencontre_paris_hosted');

    let lieu_rencontre = $('#lieu_rencontre');
    let span_domicile = $('#domicile');
    let span_exterieur = $('#exterieur');

    $('.modal').modal();

    $("#dropdowner").dropdown();

    $('select').formSelect();

    $('.sidenav').sidenav({
        closeOnClick: true,
        draggable: true,
        preventScrolling: true
    });

    $('.tabs').tabs();

    $('#competiteur_avatar').on('keyup', function () {
        $('#img_competiteur_avatar').attr("src", $('#competiteur_avatar').val());
    });

    $('#img_competiteur_avatar').on('error', function () {
        M.toast({html: 'Cette image n\'est pas valide'});
        $('#img_competiteur_avatar').attr("src", 'https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png');
        $('#competiteur_avatar').val('https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png');
    });

    /*
     * Au démarrage de la page
     */
    if (exemptDep.is(':checked')){
        adversaireDep.val("").attr('placeholder', "Pas d'adversaire");
        adversaireDep.prop('disabled', true);
        lieu_rencontre.prop('checked', false).prop('disabled', true);
        hostedDep.prop('checked', false).prop('disabled', true);
    }
    else adversaireDep.prop('disabled', false);

    if (exemptPar.is(':checked')){
        adversairePar.val("").attr('placeholder', "Pas d'adversaire");
        adversairePar.prop('disabled', true);
        lieu_rencontre.prop('checked', false).prop('disabled', true);
        hostedPar.prop('checked', false).prop('disabled', true);
    }
    else adversairePar.prop('disabled', false);

    /*
     * Triggers
     */
    exemptDep.change(function() {
        if(this.checked){
            lieu_rencontre.prop('checked', false).prop('disabled', true);
            hostedDep.prop('checked', false).prop('disabled', true);
            adversaireDep.val("").attr('placeholder', "Pas d'adversaire");
            adversaireDep.prop('disabled', true);
        }
        else{
            adversaireDep.attr('placeholder', "Adversaire");
            adversaireDep.prop('disabled', false);
            lieu_rencontre.prop('disabled', false);
        }
    });

    exemptPar.change(function() {
        if(this.checked){
            lieu_rencontre.prop('checked', false).prop('disabled', true);
            hostedPar.prop('checked', false).prop('disabled', true);
            adversairePar.val("").attr('placeholder', "Pas d'adversaire");
            adversairePar.prop('disabled', true);
        }
        else{
            adversairePar.attr('placeholder', "Adversaire");
            adversairePar.prop('disabled', false);
            lieu_rencontre.prop('disabled', false);
        }
    });

    lieu_rencontre.change(function() {
        if(this.checked){
            hostedDep.prop('checked', false).prop('disabled', true);
            hostedPar.prop('checked', false).prop('disabled', true);
            span_domicile.css("font-weight", "normal");
            span_exterieur.css("font-weight", "bold");
        }
        else{
            hostedPar.prop('disabled', false);
            hostedDep.prop('disabled', false);
            span_domicile.css("font-weight", "bold");
            span_exterieur.css("font-weight", "normal");
        }
    });

    $('li[id^="select-options"]').on('touchend', function (e) {
        e.stopPropagation();
    });
});