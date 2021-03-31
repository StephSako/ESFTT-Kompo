function contact(idCompetiteur, idMail)
{
    if (!$('#sujetMail' + idMail + idCompetiteur).val() || !$('#messageMail' + idMail + idCompetiteur).val()) {
        M.toast({html: 'Renseignez un sujet et un message'});
    } else {
        sending();

        $.ajax({
            url : '/contact/' + idCompetiteur + '/' + idMail,
            type : 'POST',
            data: {
                sujet: $('#sujetMail' + idMail + idCompetiteur).val(),
                message: $('#messageMail' + idMail + idCompetiteur).val(),
                importance: $('#importance' + idMail + idCompetiteur).is(":checked")
            },
            dataType : 'json',
            success : function(response)
            {
                endSending(response.message);
            },
            error : function()
            {
                endSending('Une erreur est survenue !');
            }
        });
    }
}

function sending(){
    $("[id='preloaderSendMail']").show();
    $("[id='btnSendMail']").hide();
}

function endSending(message){
    $("[id='preloaderSendMail']").hide();
    $("[id='btnSendMail']").show();
    M.toast({html: message});
}

$(document).ready(function()
{
    $("[id='preloaderSendMail']").hide();
});