function setModal(identifiant){
    $('#btnSendMail' + identifiant).addClass('disabled');

    $('#sujetMail' + identifiant).on('keyup', function () {
        if ($('#sujetMail' + identifiant).val() && $('#messageMail' + identifiant).val())
            $('#btnSendMail' + identifiant).removeClass('disabled');
        else $('#btnSendMail' + identifiant).addClass('disabled');
    });

    $('#messageMail' + identifiant).on('keyup', function () {
        if ($('#sujetMail' + identifiant).val() && $('#messageMail' + identifiant).val())
            $('#btnSendMail' + identifiant).removeClass('disabled');
        else $('#btnSendMail' + identifiant).addClass('disabled');
    });
}