function resetPassword() {
    if (!$('#username').val() || !$('#email').val()) M.toast({html: 'Renseignez votre pseudo et votre adresse mail'});
    else {
        sending();
        $.ajax({
            url : '/login/reset_password',
            type : 'POST',
            data: {
                mail: $('#email').val(),
                username: $('#username').val(),
            },
            dataType : 'json',
            success : function(response) { endSending(response.message); },
            error : function(error) { console.log(error); endSending('Une erreur est survenue !'); }
        });
    }
}

function sending(){
    $("#preloaderResetPassword").show();
    $('#buttonsResetPassword').hide();
    $('#email').prop('disabled', true);
    $('#username').prop('disabled', true);
}

function endSending(message){
    $('#btnResetPassword').addClass('disabled');
    $("#preloaderResetPassword").hide();
    $('#buttonsResetPassword').show();
    M.toast({html: message});
    $('#email').val('').prop('disabled', false);
    $('#username').val('').prop('disabled', false);
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