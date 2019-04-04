var valid = true;

jQuery.validator.addMethod(
    "multiemail",
    function (value, element) {
        var email = value.split(/[;,]+/); // split element by , and ;
        valid = true;
        for (var i in email) {
            value = email[i];
            if ($.trim(value) != "" ){
                valid = valid && jQuery.validator.methods.email.call(this, $.trim(value), element);
            }
        }
        return valid;
    },
    jQuery.validator.messages.multiemail
);

function validateForm(){
    $("#mailExportForm").trigger('keyup');
    $("#mailExportForm").validate({
        debug:true,
        rules: {
                email: {
                    required: true,
                    multiemail: true
                }
            },
        messages: {
                email: {
                    required: "You must complete the email address field.",
                    multiemail: "You must enter a valid email,<br/> or multiple emails separated by commas."
                }
            },
        errorLabelContainer:"#mailError",
        errorElement:'span',
    });
}