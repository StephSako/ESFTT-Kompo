let urlNewDispo            = '/journee/disponibilite/new/' + numJournee + '/';
let urlUpdateDispo         = '/journee/disponibilite/update/' + dispoJoueur + '/';

let preloader              = $("#preloaderSetDispo");

let sectionDispoDefined    = $("#btnsDispoDefined");
let sectionDispoUndefined  = $("#btnsDispoUnDefined");

let btnDefinedSetDispo     = $("#btnsDispoDefined #dispoSetDispo");
let btnDefinedSetIndispo   = $("#btnsDispoDefined #dispoSetIndispo");

let btnUndefinedSetDispo   = $("#btnsDispoUndefined #dispoSetDispo");
let btnUndefinedSetIndispo = $("#btnsDispoUndefined #dispoSetIndispo");

function setDispo(newMode, url, dispo)
{
    //console.log(url + dispo)
    /*if (disponible === -1){
        sectionDispoDefined.removeAttr('hidden');
        sectionDispoUndefined.attr('hidden', 'true');
    } else {
        sectionDispoDefined.attr('hidden', 'true');
        sectionDispoUndefined.removeAttr('hidden');
    }*/

    sending();
    $.ajax({
        url : url + dispo,
        type : 'POST',
        dataType : 'json',
        success : function(response)
        {
            endSending(response, newMode);
        },
        error : function()
        {
            endSending('Une erreur est survenue !');
        }
    });
}

function sending(){
    preloader.show();
    //sectionDispoDefined.hide();
    //sectionDispoUndefined.hide();

    /*$('#btnSendMail' + idMail + idReceiver).hide();
    $(sujetInput).prop('disabled', true);
    $(messageInput).prop('disabled', true);
    $(importanceInput).prop('disabled', true);*/
}

function endSending(data, newMode){
    preloader.hide();
    //sectionDispoDefined.show();
    //sectionDispoUndefined.hide();

    //dispoJoueur = data.dispoJoueur;
    M.toast({html: data.message});
    M.toast({html: data.data});
    /*$('#btnSendMail' + idMail + idReceiver).show().addClass('disabled');

    $(sujetInput).val('').prop('disabled', false);
    $(messageInput).val('').prop('disabled', false);
    $(importanceInput).prop('checked', false).prop('disabled', false);
    $('.modal').modal('close');*/
}

btnDefinedSetDispo.on('click', function() {
    setDispo(false, urlUpdateDispo, '1');
})

btnDefinedSetIndispo.on('click', function() {
    let result = confirm('Vous risquez d\'être désélectionné. Continuer ?')
    if (result) setDispo(false, urlUpdateDispo, '0');
})

btnUndefinedSetDispo.on('click', function() {
    setDispo(true, urlNewDispo, '1');
})

btnUndefinedSetIndispo.on('click', function() {
    setDispo(true, urlNewDispo, '0');
})