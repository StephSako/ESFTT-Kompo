function resetPassword() {
    if (!$('#username').val() || !$('#email').val()) M.toast({html: 'Renseignez votre pseudo et votre adresse e-mail'});
    else {
        sending();
        $.ajax({
            url : '/login/contact/forgotten_password',
            type : 'POST',
            data: {
                mail: $('#email').val(),
                username: $('#username').val(),
            },
            dataType : 'json',
            success : function(response) { endSending(response.message, response.success); },
            error : function() { endSending('Une erreur est survenue !', false); }
        });
    }
}

function sending(){
    $("#preloaderResetPassword").show();
    $('#buttonsResetPassword').hide();
    $('#email').prop('disabled', true);
    $('#username').prop('disabled', true);
}

function endSending(message, success){
    $("#preloaderResetPassword").hide();
    $('#buttonsResetPassword').show();
    $('#email').prop('disabled', false);
    $('#username').prop('disabled', false);

    if (!success) M.toast({html: message});
    else {
        $('#divMailSent').removeClass('hide');
        $('#sendMailForm').addClass('hide');
    }
}

$(document).ready(function() {
    $('#username').on('keyup', function () {
        if ($('#username').val() && $('#email').val()) $('#btnResetPassword').removeClass('disabled');
        else $('#btnResetPassword').addClass('disabled');
    });

    $('#email').on('keyup', function () {
        if ($('#username').val() && $('#email').val()) $('#btnResetPassword').removeClass('disabled');
        else $('#btnResetPassword').addClass('disabled');
    });
});