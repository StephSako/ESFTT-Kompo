function sendMail(type, idCompo)
{
    $.getJSON('/notifySelectedPlayers/' + type + '/' + idCompo + '/' + $('#titreAlertSelectedPlayers').val().replace('/', "-") + '/' + $('#messageAlertSelectedPlayers').val().replace('/', "-"), function (data)
    {
        sending();
        M.toast({html: data.message});
    })
    .fail(function ()
    {
        endSending();
        M.toast({html: 'Une erreur est survenue ...'});
    })
    .done(function(){
        endSending();
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