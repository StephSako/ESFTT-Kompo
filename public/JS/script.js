$(document).ready(function() {
    $('.modal').modal();

    $('select').formSelect();

    $(".dropdown-trigger").dropdown();

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

    if ($('#back_office_rencontre_departementale_domicile').is(':checked')) $("#back_office_rencontre_departementale_hosted").removeAttr("disabled");
    else $("#back_office_rencontre_departementale_hosted").attr("disabled", true);

    if ($('#back_office_rencontre_paris_domicile').is(':checked')) $("#back_office_rencontre_paris_hosted").removeAttr("disabled");
    else $("#back_office_rencontre_dparis_hosted").attr("disabled", true);

    $("#back_office_rencontre_departementale_domicile").change(function() {
        if(this.checked) $("#back_office_rencontre_departementale_hosted").removeAttr("disabled");
        else{
            $("#back_office_rencontre_departementale_hosted").prop("checked", false).attr("disabled", true);
        }
    });

    $("#back_office_rencontre_paris_domicile").change(function() {
        if(this.checked) $("#back_office_rencontre_paris_hosted").removeAttr("disabled");
        else{
            $("#back_office_rencontre_paris_hosted").prop("checked", false).attr("disabled", true);
        }
    });
});