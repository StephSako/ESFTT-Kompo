function contact(idCompetiteur, mail, idMail)
{
    if (!$('#sujetMail' + idMail + idCompetiteur).val() || !$('#messageMail' + idMail + idCompetiteur).val()) {
        M.toast({html: 'Renseignez un sujet et un message'});
    } else {
        sending();
        $.getJSON('/contact/' + $('#sujet').val().replace('/', "-") + '/' + $('#message').val().replace('/', "-"), function (data)
        {
            M.toast({html: data.message});
        })
        .fail(function ()
        {
            endSending();
            M.toast({html: 'Une erreur est survenue ...'});
            $('.modal').close();
        })
        .done(function(){
            endSending();
            $('.modal').close();
        });
    }
}

function notifySelectedPlayers(type, idCompo)
{
    sending();
    $.getJSON('/notifySelectedPlayers/' + type + '/' + idCompo + '/' + $('#titreAlertSelectedPlayers').val().replace('/', "-") + '/' + $('#messageAlertSelectedPlayers').val().replace('/', "-"), function (data)
    {
        M.toast({html: data.message});
    })
    .fail(function ()
    {
        endSending();
        M.toast({html: 'Une erreur est survenue ...'});
        $('.modal').close();
    })
    .done(function(){
        endSending();
        $('.modal').close();
    });
}

function sending(){
    $("[id='preloaderSendMail']").show();
    $("[id='btnSendMail']").hide();
}

function endSending(){
    $("[id='preloaderSendMail']").hide();
    $("[id='btnSendMail']").show();
}

$(document).ready(function()
{
    $("[id='preloaderSendMail']").hide();
});