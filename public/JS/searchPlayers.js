let ngTdTableSearch = undefined;

$(document).ready(function() {
    ngTdTableSearch = $(".search-table tbody tr:first-child")[0]?.children.length ?? 10
});

$("#search-input").on("keyup", function() {
    let value = $(this).val().toUpperCase();
    let trs = $(".search-table tbody tr");

    trs.each(function(_index) {
        let row = $(this);
        let tr_empty_result = $('.search-table tbody tr#tr-empty-result-search')[0]

        if (value.length && row[0].id !== 'tr-empty-result-search') {
            let nomPrenom = row.find("td:nth-child(3)").text().trim().toUpperCase();
            let rolesTemp = Array.from(row.find("td:first")[0].children).filter(e => e.classList.contains('badge')).map(e => e.dataset.badgeCaption.toUpperCase())

            if ((nomPrenom.indexOf(value) < 0 && rolesTemp.filter(role => role.indexOf(value) >= 0).length === 0)) row.hide();
            else row.show();

            let tr_array = Array.from(trs);
            let tr_hidden = tr_array.filter(e => e.style.display === 'none' && e.id !== 'tr-empty-result-search')
            let tr_displayed = tr_array.filter(e => e.style.display !== 'none' && e.id !== 'tr-empty-result-search')
            let tr_results = tr_array.filter(e => e.id !== 'tr-empty-result-search')

            // S'il n'y a pas de résultats
            if (!tr_empty_result) {
                if (tr_results.length === tr_hidden.length) {
                    $('.search-table tbody:last-child').append('<tr style="background-color: transparent" id="tr-empty-result-search"><td colspan="' + ngTdTableSearch + '"><i>Pas de résultat pour votre recherche</i></td></tr>');
                }
            }
            // On supprime le <tr> de résultat vide s'il y a des résultats
            else if (tr_displayed.length) tr_empty_result.remove()
        } else {
            if (tr_empty_result && !Array.from(trs).filter(e => e.style.display === 'none' && e.id !== 'tr-empty-result-search').length) tr_empty_result.remove()
            row.show();
        }
    });
});