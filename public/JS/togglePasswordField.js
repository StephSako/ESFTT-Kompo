$(document).ready(function () {
    let clicked = {};
    $(".toggle-password").click(function () {
        $(this).toggleClass("toggle-password");

        if (clicked[$(this)[0]['id']] === undefined || clicked[$(this)[0]['id']] === 0) {
            $(this).html('<span class="material-icons">visibility_off</span >');
            clicked[$(this)[0]['id']] = 1;
        } else {
            $(this).html('<span class="material-icons">visibility</span >');
            clicked[$(this)[0]['id']] = 0;
        }

        let input = $($(this).attr("ontoggle"));
        input.attr("type", input.attr("type") === "password" ? "text" : "password");
    });
});