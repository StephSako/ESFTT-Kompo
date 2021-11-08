function getClassementVirtualPoints() {
    $.ajax({
        url : '/journee/classement-virtual-points',
        type : 'POST',
        dataType : 'json',
        success : function(responseTemplate) { templatingClassementVirtualPoints(responseTemplate); },
        error : function() { templatingClassementVirtualPoints("<p style='margin-top: 10px' class='pastille reset red'>Le service de la FFTT rencontre des perturbations. Réessayez plus tard</p>"); }
    });
}

function templatingClassementVirtualPoints(response){
    $('#rankingContent').html(response);
}