$(window).resize(function() {
    let trs = [...document.getElementById('table-to-adapt-columns').rows];
    if ($(window).width() <= 983) {
        trs.forEach(element => element.style.height = Math.max(...trs.map(x => x.offsetHeight)) + 'px');
    } else trs.forEach(element => element.style.height = 'auto');
});