$(document).ready(function() {
    let selectedPlayersArray = $(".form_div_list .select-wrapper select[id=rencontre_idJoueur0] option[value!='']");
    let ulsToCustom = Array.from($('.form_div_list .select-wrapper ul.dropdown-content.select-dropdown'));
    ulsToCustom.forEach(ul => {
        Array.from(ul.children)
            .filter(li => li.className.includes('optgroup-option') && li.id.startsWith('select-options-'))
            .forEach((li, index) => {
                if (selectedPlayersArray[index].className.includes('SELECTED')) {
                    const spanContent = li.children[0].innerHTML;
                    li.innerHTML =
                        "<div style='display: flex; justify-content: flex-start; align-items: center;'>" +
                            "<i class='material-icons green-text lighten-2' style='font-size: 1.5rem; padding: 14px 0 14px 5px;'>check</i>" +
                            "<span style='font-size: 16px; line-height: 22px; padding: 14px 16px 14px 5px;'>" + spanContent + "</span>" +
                        "</div>"
                }
            })
    })
});