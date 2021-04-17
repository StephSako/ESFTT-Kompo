$(document).ready(function() {
    $('.modal').modal();

    $(".dropdown-trigger").dropdown();

    $('select').formSelect();

    $('.sidenav').sidenav({
        closeOnClick: true,
        draggable: true,
        preventScrolling: true
    });

    $('.tabs').tabs();

    $('.tooltipped').tooltip();

    $('li[id^="select-options"]').on('touchend', function (e) {
        e.stopPropagation();
    });

    $('.collapsible').collapsible({
        accordion : false
    });
});