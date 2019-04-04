$(document).ready(function(){
    
    var wrapper = document.getElementById("signature-pad"),
    clearButton = wrapper.querySelector("[data-action=clear]"),
    saveButton = wrapper.querySelector("[data-action=save]"),
    canvas = wrapper.querySelector("canvas"),
    signaturePad;

    signaturePad = new SignaturePad(canvas);

    clearButton.addEventListener("click", function (event) {
        signaturePad.clear();
        $(canvas).attr('style','background:'+$("#sigPhoto").css('background'));
    });
    
    saveButton.addEventListener("click", function (event) {
        if (signaturePad.isEmpty()) {
            $("#sigError").text("Please provide signature first.");
        } else {
            $("#sigError").text('');
            $(".m-signature-pad--footer").addClass('hidden');
            var photoContent = signaturePad.toDataURL();
            signaturePad.clear();
            $(canvas).addClass('hidden');
            $("#sigPhoto").css('background','url('+photoContent+')');
            $("#sigPhoto").removeClass('hidden');
            $("#editProfileForm").append('<input type="hidden" name="signature" value="'+photoContent+'"/>');
            $("#editProfileButton").removeClass('hidden');
//            window.open(signaturePad.toDataURL());
        }
    });
    
    $(canvas).click(function(){
        $(canvas).removeAttr('style');
        $("#useSignatureButton").removeClass('hidden');
        $("#editProfileButton").addClass('hidden');
    });
    
    $("#review-button").click(function(){
        var formData = $("#review-details").serialize();
        $.post(site+'cards/review', formData, function(response){
                if(response.status){
                    location.reload();
                }
            },'json');
    });

    $("#modalProfile #editProfileButton").click(function(){
        var formData = $("#editProfileForm").serialize();
        var modal = '#modalProfile';

        $.post(site+'profile', formData, function(response){
            clearEditErrors();
            if(response.status){
               $(modal).modal('hide');
               location.reload(true);
            }else{
                $.each(response.errors, function(index, value){
                    if (index == 'signature'){
                        $("#sigError").text(value[0]);
                    }else{
                        var input = $("#profile_"+index, modal);
                        $(input).parent().parent().addClass('error');
                        $(input).parent().append('<span class="help-inline">'+value[0]+'</span>');
                    }
                }); 
           }
        },'json');
    });

    function clearEditErrors(){
        $('#modalSignatureCanvas .help-inline').remove();
        $('#modalSignatureCanvas .control-group').removeClass('error');
        
    }
    
});

