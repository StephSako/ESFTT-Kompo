$(document).ready(function() {
    $('.modal').modal();

    $("html").niceScroll({
        cursorcolor:"#023a72",
        cursorwidth:"10px"
    });

    $('select').formSelect();

    $('.sidenav').sidenav({
        closeOnClick: true
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
});