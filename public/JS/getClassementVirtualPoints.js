function getClassementVirtuel() {
    if (!alreadyCalledClassement) {
        alreadyCalledClassement = true;
        $.ajax({
            url : '/journee/classement-virtuel',
            type : 'POST',
            dataType : 'json',
            success : function(responseTemplate) { templatingClassementVirtuel(responseTemplate); },
            error : function() { templatingClassementVirtuel("<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>"); }
        });
    }
}

function templatingClassementVirtuel(response){
    $('#rankingContent').html(response);
}

let alreadyCalledClassement = false;