$(window).resize(function () {
    let tables = document.getElementsByClassName('table-to-adapt-columns')
    for (const table of tables) {
        if (table.offsetParent != null) {
            let trs = [...table.rows].filter(tr => tr.id !== "tr-empty-result-search");
            if ($(window).width() <= 983) {
                let newHeight = Math.max(...trs.map(x => x.offsetHeight));
                if (newHeight) trs.forEach(element => element.style.height = newHeight + 'px');
            } else trs.forEach(element => element.style.height = 'auto');
        }
    }
});

$(document).ready(function () {
    resizeTable()
});

let resized = false;
let observer = $('#modalcustom').length ? new MutationObserver((mutations) => {
    if (!resized) {
        mutations.forEach(function () {
            if ($('#modalcustom').css('display') !== 'none' && !resized) {
                resizeTable()
                resized = true
            }
        });
    }
}) : {
    observe: (_obj) => {
    }
};

observer.observe(document.querySelector('#modalcustom'), {
    attributes: true,
    attributeFilter: ['style']
});