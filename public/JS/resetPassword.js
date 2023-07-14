function resetPassword() {
    if (!$('#username').val() || !$('#email').val()) M.toast({html: 'Renseignez votre pseudo et votre adresse e-mail'});
    else {
        sendingResetPassword();
        $.ajax({
            url: '/login/contact/forgotten-password',
            type: 'POST',
            data: {
                mail: $('#email').val(),
                username: $('#username').val(),
            },
            dataType: 'json',
            success: (response) => {
                endSendingResetPassword(response.message, response.success);
            },
            error: () => {
                endSendingResetPassword('Une erreur est survenue !', false);
            }
        });
    }
}

function sendingResetPassword() {
    $("#preloaderResetPassword").show();
    $('#buttonsResetPassword').hide();
    $('#email').prop('disabled', true);
    $('#username').prop('disabled', true);
}

function endSendingResetPassword(message, success) {
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

$(document).ready(() => {
    $('#username').on('keyup', () => {
        if ($('#username').val() && $('#email').val()) $('#btnResetPassword').removeClass('disabled');
        else $('#btnResetPassword').addClass('disabled');
    });

    $('#email').on('keyup', () => {
        if ($('#username').val() && $('#email').val()) $('#btnResetPassword').removeClass('disabled');
        else $('#btnResetPassword').addClass('disabled');
    });
});