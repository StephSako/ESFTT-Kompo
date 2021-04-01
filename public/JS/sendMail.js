function contact(idReceiver, idMail, mailReceiver, nomReceiver)
{
    console.log($('#selectMailSender' + idMail + idReceiver).val())
    /*if (!$('#sujetMail' + idMail + idReceiver).val() || !$('#messageMail' + idMail + idReceiver).val()) {
        M.toast({html: 'Renseignez un sujet et un message'});
    } else {
        sending(idMail, idReceiver, '#sujetMail' + idMail + idReceiver, '#messageMail' + idMail + idReceiver, '#importance' + idMail + idReceiver);

        $.ajax({
            url : '/contact/message',
            type : 'POST',
            data: {
                nomReceiver: nomReceiver,
                mailReceiver: mailReceiver,
                sujet: $('#sujetMail' + idMail + idReceiver).val(),
                message: $('#messageMail' + idMail + idReceiver).val(),
                importance: $('#importance' + idMail + idReceiver).is(":checked")
            },
            dataType : 'json',
            success : function(response)
            {
                endSending(idMail, idReceiver, response.message, '#sujetMail' + idMail + idReceiver, '#messageMail' + idMail + idReceiver, '#importance' + idMail + idReceiver);
            },
            error : function()
            {
                endSending(idMail, idReceiver, 'Une erreur est survenue !', '#sujetMail' + idMail + idReceiver, '#messageMail' + idMail + idReceiver, '#importance' + idMail + idReceiver);
            }
        });
    }*/
}

function sending(idMail, idReceiver, sujetInput, messageInput, importanceInput){
    $("[id='preloaderSendMail']").show();
    $('#btnSendMail' + idMail + idReceiver).hide();
    $(sujetInput).prop('disabled', true);
    $(messageInput).prop('disabled', true);
    $(importanceInput).prop('disabled', true);
}

function endSending(idMail, idReceiver, message, sujetInput, messageInput, importanceInput){
    $("[id='preloaderSendMail']").hide();
    $('#btnSendMail' + idMail + idReceiver).show().addClass('disabled');
    M.toast({html: message});
    $(sujetInput).val('').prop('disabled', false);
    $(messageInput).val('').prop('disabled', false);
    $(importanceInput).prop('checked', false).prop('disabled', false);
    $('.modal').modal('close');
}

$(document).ready(function()
{
    $("[id='preloaderSendMail']").hide();
});