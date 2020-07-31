$(document).ready(function() {
    $("html").niceScroll({
        cursorcolor:"#012242",
        cursorwidth:"10px"
    });

    $("select").niceSelect();

    $('.tabs').tabs();

    $('.datepicker').datepicker({
        firstDay: 1,
        i18n: {
            months: [ 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ],
            monthsShort: [ 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Déc' ],
            weekdays: [ 'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi' ],
            weekdaysShort: [ 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ],
            weekdaysAbbrev: [ 'D', 'L', 'M', 'M', 'J', 'V', 'S' ],
            cancel: 'Annuler'
        }
    });

    $('#competiteur_avatar').on('keyup', function () {
        $('#img_competiteur_avatar').attr("src", $('#competiteur_avatar').val());
    });
    $('#img_competiteur_avatar').on('error', function () {
        M.toast({html: 'Cette image n\'est pas valide'});
        $('#img_competiteur_avatar').attr("src", 'https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png');
        $('#competiteur_avatar').val('https://cdn1.iconfinder.com/data/icons/ui-next-2020-shopping-and-e-commerce-1/12/75_user-circle-512.png');
    });
});