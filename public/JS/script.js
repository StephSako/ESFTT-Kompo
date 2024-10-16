function resizeTable() {
    if ($(window).width() <= 992) {
        let tables = document.getElementsByClassName('table-to-adapt-columns')
        for (const table of tables) {
            if (table.offsetParent != null) {
                let trs = [...table.rows].filter(tr => tr.id !== "tr-empty-result-search");
                let newHeight = Math.max(...trs.map(x => x.offsetHeight));
                if (newHeight) trs.forEach(tr => tr.style.height = newHeight + 'px');
            }
        }
    }
}

function jumpTo(anchor) {
    window.location.href = '#' + anchor;
    $(window).scrollTop($(window).scrollTop() - 80);
}

function copyPaste(value, message, modalId = null) {
    let temp = $("<textarea>");
    let brRegex = /%0D%0A/gi;
    $(modalId != null ? '#' + modalId : '#main').append(temp);
    temp.val(value.replace(brRegex, "\r\n")).select();
    document.execCommand("copy");
    temp.remove();
    M.toast({html: message})
}

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
            resizeTable()
        }
    });

    $('.tooltipped').tooltip();

    $('li[id^="select-options"]').on('touchend', function (e) {
        e.stopPropagation();
    });

    $('.collapsible').collapsible({
        accordion: false
    });

    $('.fixed-action-btn').floatingActionButton();

    Array.from($('.trimInput')).forEach(el => el.addEventListener('input', () => {
        el.value = el.value.trim();
    }))
});