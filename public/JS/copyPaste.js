function copyPaste(id_rencontre) {
    const adresse = document.querySelector('p.adresse_' + id_rencontre);
    const range = document.createRange();
    range.selectNode(adresse);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    document.execCommand('copy');
    M.toast({html: 'Adresse copiée dans le presse-papier'})
}