let searchValue = (table_id, input_id, i_nomPrenom, i_roles) => {
    let value = $(input_id).val().toUpperCase();
    let trs = $(table_id + " tbody tr");

    trs.each(function (_index) {
        let row = $(this);
        let tr_empty_result = $(table_id + ' tbody tr#tr-empty-result-search')[0]

        if (value.length && row[0].id !== 'tr-empty-result-search') {
            let nomPrenom = row.find("td:" + (i_nomPrenom === 0 ? "first" : "nth-child(" + i_nomPrenom + ")")).text().trim().toUpperCase();
            let rolesTemp = Array.from(row.find("td:" + (i_roles === 0 ? "first" : "nth-child(" + (i_roles === 1 ? i_roles += 1 : i_roles) + ")"))[0].children).filter(e => e.classList.contains('badge')).map(e => e.dataset.badgeCaption.toUpperCase())

            if ((nomPrenom.indexOf(value) < 0 && rolesTemp.filter(role => role.indexOf(value) >= 0).length === 0)) row.hide();
            else row.show();

            let tr_array = Array.from(trs);
            let tr_hidden = tr_array.filter(e => e.style.display === 'none' && e.id !== 'tr-empty-result-search')
            let tr_displayed = tr_array.filter(e => e.style.display !== 'none' && e.id !== 'tr-empty-result-search')
            let tr_results = tr_array.filter(e => e.id !== 'tr-empty-result-search')

            // S'il n'y a pas de résultats
            if (!tr_empty_result) {
                if (tr_results.length === tr_hidden.length) {
                    $(table_id + ' tbody:last-child').append('<tr style="background-color: transparent" id="tr-empty-result-search"><td colspan="' + ($(table_id + " tbody tr:first-child")[0]?.children.length ?? 10) + '"><i>Pas de résultat pour votre recherche</i></td></tr>');
                }
            }
            // On supprime le <tr> de résultat vide s'il y a des résultats
            else if (tr_displayed.length) tr_empty_result.remove()
        } else {
            if (tr_empty_result && !Array.from(trs).filter(e => e.style.display === 'none' && e.id !== 'tr-empty-result-search').length) tr_empty_result.remove()
            row.show();
        }
    });
}

function addRemoveCustomContact(isChecked, idCompetiteur, nomPrenom, urlAvatarPic) {
    if (isChecked) $('#listSelectedContacts').append(`<div id="${idCompetiteur}" class="chip customMessage"><img src="https://www.prive.esftt.com/media/cache/thumb/images/${urlAvatarPic ? 'profile_pictures/' + urlAvatarPic : 'account.png'}" alt="Icon custom contact">${nomPrenom}</div>`)
    else $(`#listSelectedContacts .chip.customMessage#${idCompetiteur}`).remove();

    if (Array.from($(`#listSelectedContacts .chip.customMessage`)).length) $('#divSelectedContacts').removeAttr('hidden');
    else $('#divSelectedContacts').attr('hidden', '');
}

let divCustomContactsCheckList = undefined;

function getInfosContactsSelectedPlayers() {
    divCustomContactsCheckList = `<div>${$('#modalcustom .modal-content')[0].innerHTML}</div>`;
    let divCustomContactsCheckListElement = $($.parseHTML(divCustomContactsCheckList))
    let checkedIDs = Array.from($('#listSelectedContacts .chip.customMessage')).map(chip => chip.id)
    divCustomContactsCheckListElement.find('table#search-table-custom input:checkbox').removeAttr('checked')
    let inputFiltre = $('input#search-input-custom');
    divCustomContactsCheckListElement.find('input#search-input-custom').attr('value', inputFiltre[0] ? inputFiltre[0].value : '')
    divCustomContactsCheckListElement.find(checkedIDs.map(id => `table#search-table-custom input#${id}:checkbox`).join(', ')).attr('checked', 'checked')
    divCustomContactsCheckList = divCustomContactsCheckListElement.html()
    $('#customMessageLoader').removeClass('hide');
    $('button#btn_display_medias').addClass('hide');
    $('i#removeAllCustomContacts').addClass('hide');
    $('input:checkbox').attr('disabled', 'disabled');

    $.ajax({
        url: '/contacter/custom-infos-contact',
        type: 'GET',
        data: {
            contactIDs: checkedIDs
        },
        dataType: 'json',
        success: (response) => {
            endSendingGetInfosContact(response, true);
        },
        error: () => {
            endSendingGetInfosContact(null, false);
        }
    });
}

function endSendingGetInfosContact(response, isOK) {
    if (isOK) {
        let HTMLResponse = $.parseHTML(response)[1].firstElementChild.innerHTML
        $('#modalcustom .modal-content').html(HTMLResponse);
        $('#listSelectedContacts .chip.customMessage').remove()
    } else {
        $('#customMessageLoader').addClass('hide');
        $('button#btn_display_medias').removeClass('hide');
        $('i#removeAllCustomContacts').removeClass('hide');
        $('input:checkbox').removeAttr('disabled');
        M.toast({html: 'Une erreur est survenue'});
    }
}

function backToCustomContactsCheckList() {
    $('#modalcustom .modal-content').html(divCustomContactsCheckList);
}

function removeSelection() {
    $('#listSelectedContacts .chip.customMessage').remove()
    $('#divSelectedContacts').attr('hidden', '')
    $('table#search-table-custom input:checkbox').prop('checked', false);
}