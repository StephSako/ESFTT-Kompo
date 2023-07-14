$(window).resize(function () {
    let tables = document.getElementsByClassName('table-to-adapt-columns')
    for (const table of tables) {
        if (table.offsetParent != null) {
            let trs = [...table.rows];
            if ($(window).width() <= 983) {
                trs.forEach(element => element.style.height = Math.max(...trs.map(x => x.offsetHeight)) + 'px');
            } else trs.forEach(element => element.style.height = 'auto');
        }
    }
});

function resizeTable() {
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

$(document).ready(function () {
    resizeTable()
});

let resized = false;
let observer = new MutationObserver(function (mutations) {
    if (!resized) {
        mutations.forEach(function (mutation) {
            if ($('#modalcustom').css('display') !== 'none' && !resized) {
                resizeTable()
                resized = true
            }
        });
    }
});

observer.observe(document.querySelector('#modalcustom'), {
    attributes: true,
    attributeFilter: ['style']
});