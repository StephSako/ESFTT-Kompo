$(document).ready(() => {
    $('.modal').modal();

    $(".dropdown-trigger").dropdown({
        coverTrigger: false
    });

    $('select').formSelect();

    $('.sidenav').sidenav({
        closeOnClick: true,
        draggable: true,
        preventScrolling: true
    });

    $('.tabs').tabs({
        onShow: () => {
            if ($(window).width() <= 992) {
                let tables = document.getElementsByClassName('table-to-adapt-columns')
                for (const table of tables) {
                    if (table.offsetParent != null) {
                        let trs = [...table.rows];
                        trs.forEach(tr => tr.style.height = Math.max(...trs.map(x => x.offsetHeight)) + 'px');
                    }
                }
            }
        }
    });

    $('.tooltipped').tooltip();

    $('li[id^="select-options"]').on('touchend', function (e) {
        e.stopPropagation();
    });

    $('.collapsible').collapsible({
        accordion : false
    });

    $('.fixed-action-btn').floatingActionButton();
});